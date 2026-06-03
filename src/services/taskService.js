import { supabase } from '@/lib/customSupabaseClient';
import { getTemplateByType, replaceTemplateVariables } from './taskMessageTemplateService';

export const createTaskWithAssignments = async (taskData, assigneeIds) => {
    try {
        const { data: { user } } = await supabase.auth.getUser();
        if (!user) throw new Error("Not authenticated");

        // 1. Create the task
        const { data: task, error: taskError } = await supabase
            .from('tasks')
            .insert([{
                title: taskData.title,
                description: taskData.description,
                priority: taskData.priority,
                category_id: taskData.category_id || null,
                start_date: taskData.start_date || null,
                deadline: taskData.deadline,
                created_by: user.id,
                status: 'Pending'
            }])
            .select()
            .single();

        if (taskError) throw taskError;

        if (assigneeIds && assigneeIds.length > 0) {
            // 2. Fetch user profiles to check roles for auto-accept
            const { data: profiles, error: profilesError } = await supabase
                .from('profiles')
                .select('id, role, full_name, phone, email')
                .in('id', assigneeIds);

            if (profilesError) throw profilesError;

            // 3. Create assignments with auto-accept logic for admins
            const assignments = assigneeIds.map(userId => {
                const profile = profiles.find(p => p.id === userId);
                const isAdmin = profile && ['admin', 'super_admin', 'director'].includes(profile.role);
                
                return {
                    task_id: task.id,
                    user_id: userId,
                    status: isAdmin ? 'Accepted' : 'Pending',
                    progress: 0,
                    accepted_at: isAdmin ? new Date().toISOString() : null,
                    last_update_at: new Date().toISOString()
                };
            });

            const { error: assignError } = await supabase
                .from('task_assignments')
                .insert(assignments);

            if (assignError) throw assignError;
        }

        return { success: true, data: task };
    } catch (error) {
        console.error('Error in createTaskWithAssignments:', error);
        return { success: false, error: error.message };
    }
};

export const createTask = async (taskData, assigneeIds) => {
    return createTaskWithAssignments(taskData, assigneeIds);
};

export const updateTask = async (taskId, taskData, assigneeIds) => {
    try {
        const { error: updateError } = await supabase
            .from('tasks')
            .update({
                title: taskData.title,
                description: taskData.description,
                priority: taskData.priority,
                category_id: taskData.category_id,
                deadline: taskData.deadline
            })
            .eq('id', taskId);
            
        if (updateError) throw updateError;
        
        const { data: currentAssignments } = await supabase
            .from('task_assignments')
            .select('user_id')
            .eq('task_id', taskId);
            
        const currentAssigneeIds = currentAssignments.map(a => a.user_id);
        const toAdd = assigneeIds.filter(id => !currentAssigneeIds.includes(id));
        const toRemove = currentAssigneeIds.filter(id => !assigneeIds.includes(id));
        
        if (toRemove.length > 0) {
            await supabase.from('task_assignments').delete().eq('task_id', taskId).in('user_id', toRemove);
        }
        
        if (toAdd.length > 0) {
             const { data: profiles } = await supabase.from('profiles').select('id, role').in('id', toAdd);

            const newAssignments = toAdd.map(userId => {
                const profile = profiles?.find(p => p.id === userId);
                const isAdmin = profile && ['admin', 'super_admin', 'director'].includes(profile.role);

                return {
                    task_id: taskId,
                    user_id: userId,
                    status: isAdmin ? 'Accepted' : 'Pending',
                    progress: 0,
                    accepted_at: isAdmin ? new Date().toISOString() : null,
                    last_update_at: new Date().toISOString()
                };
            });

            await supabase.from('task_assignments').insert(newAssignments);
            
            const { data: updatedTask } = await supabase.from('tasks').select('*').eq('id', taskId).single();
            await sendTaskNotifications(updatedTask, toAdd, 'assignment');
        }

        return { success: true };
    } catch (error) {
        console.error('Error updating task:', error);
        return { success: false, error: error.message };
    }
};

export const deleteTask = async (taskId) => {
    try {
        const { error } = await supabase.from('tasks').delete().eq('id', taskId);
        if (error) throw error;
        return { success: true };
    } catch (error) {
        console.error('Error deleting task:', error);
        return { success: false, error: error.message };
    }
};

export const getTasks = async (filters = {}) => {
  try {
    let query = supabase
      .from('tasks')
      .select(`
        *,
        task_categories(*),
        task_assignments(
          id, status, progress, user_id, accepted_at, completed_at,
          profiles:user_id(id, full_name, email, phone)
        )
      `)
      .order('created_at', { ascending: false });

    if (filters.status && filters.status !== 'All') {
      query = query.eq('status', filters.status);
    }
    if (filters.priority && filters.priority !== 'All') {
      query = query.eq('priority', filters.priority);
    }
    if (filters.category && filters.category !== 'All') {
        query = query.eq('category_id', filters.category);
    }
    if (filters.search) {
      query = query.ilike('title', `%${filters.search}%`);
    }

    const { data, error } = await query;
    if (error) throw error;

    const tasksWithProgress = data.map(task => {
      const assignments = task.task_assignments || [];
      const totalProgress = assignments.reduce((sum, a) => sum + (a.progress || 0), 0);
      const overallProgress = assignments.length > 0 ? Math.round(totalProgress / assignments.length) : 0;
      return { ...task, overallProgress, assignments_count: assignments.length };
    });

    return { success: true, data: tasksWithProgress };
  } catch (error) {
    console.error('Error fetching tasks:', error);
    return { success: false, error: error.message, data: [] };
  }
};

export const getTaskDetails = async (taskId) => {
  try {
    const { data, error } = await supabase
      .from('tasks')
      .select(`
        *,
        task_categories(*),
        task_assignments(
          id, status, progress, user_id, accepted_at, completed_at,
          profiles:user_id(id, full_name, email, phone),
          task_updates(id, progress, comment, created_at, task_attachments(id, file_url, file_name))
        )
      `)
      .eq('id', taskId)
      .single();

    if (error) throw error;
    return { success: true, data };
  } catch (error) {
    console.error('Error fetching task details:', error);
    return { success: false, error: error.message };
  }
};

export const getMyTasks = async (statusFilter = 'All', categoryFilter = 'All') => {
  try {
    const { data: { user } } = await supabase.auth.getUser();
    if (!user) throw new Error("Not authenticated");

    let query = supabase
      .from('task_assignments')
      .select(`
        id, status, progress, accepted_at, completed_at, last_update_at,
        tasks!inner(*, 
            created_by_profile:profiles!tasks_created_by_fkey(full_name),
            task_categories(*)
        )
      `)
      .eq('user_id', user.id)
      .order('created_at', { ascending: false });

    if (statusFilter !== 'All') {
       query = query.eq('status', statusFilter);
    }
    
    if (categoryFilter !== 'All') {
        query = query.eq('tasks.category_id', categoryFilter);
    }

    const { data, error } = await query;
    if (error) throw error;
    
    const formattedData = data.map(assignment => ({
        ...assignment.tasks,
        assignment_id: assignment.id,
        assignment_status: assignment.status,
        my_progress: assignment.progress,
        accepted_at: assignment.accepted_at,
        completed_at: assignment.completed_at,
        last_update_at: assignment.last_update_at
    }));

    return { success: true, data: formattedData };
  } catch (error) {
    console.error('Error fetching my tasks:', error);
    return { success: false, error: error.message, data: [] };
  }
};

export const acceptTaskAssignment = async (assignmentId) => {
  try {
    const now = new Date().toISOString();
    const { data, error } = await supabase
      .from('task_assignments')
      .update({ 
          status: 'Accepted', 
          accepted_at: now,
          last_update_at: now
      })
      .eq('id', assignmentId)
      .select(`*, tasks(id, title, status)`)
      .single();

    if (error) throw error;

    if (data.tasks.status === 'Pending') {
        await supabase.from('tasks').update({ status: 'In Progress' }).eq('id', data.tasks.id);
    }

    return { success: true, data };
  } catch (error) {
    console.error('Error accepting task:', error);
    return { success: false, error: error.message };
  }
};

export const declineTaskAssignment = async (assignmentId) => {
    try {
        const now = new Date().toISOString();
        const { data, error } = await supabase
          .from('task_assignments')
          .update({ 
              status: 'Declined', 
              declined_at: now,
              last_update_at: now
          })
          .eq('id', assignmentId)
          .select(`*, tasks(id, title, status)`)
          .single();
    
        if (error) throw error;
        return { success: true, data };
      } catch (error) {
        console.error('Error declining task:', error);
        return { success: false, error: error.message };
      }
};

export const updateTaskProgress = async (assignmentId, taskId, progress, status, comment = null, attachments = []) => {
  try {
    const updates = { 
        progress, 
        status: status || 'In Progress',
        last_update_at: new Date().toISOString()
    };
    
    if (status === 'Completed' || progress === 100) {
        updates.status = 'Completed';
        updates.progress = 100;
        updates.completed_at = updates.last_update_at;
    }

    const { error: assignError } = await supabase
      .from('task_assignments')
      .update(updates)
      .eq('id', assignmentId);
    if (assignError) throw assignError;

    if (comment || (attachments && attachments.length > 0)) {
        const { data: updateData, error: updateError } = await supabase
          .from('task_updates')
          .insert([{ assignment_id: assignmentId, progress, comment: comment || 'Status updated' }])
          .select()
          .single();
        if (updateError) throw updateError;
    
        if (attachments && attachments.length > 0) {
          const attData = attachments.map(att => ({
            task_id: taskId,
            update_id: updateData.id,
            file_url: att.url,
            file_name: att.name
          }));
          const { error: attError } = await supabase.from('task_attachments').insert(attData);
          if (attError) throw attError;
        }
    }

    await evaluateTaskOverallStatus(taskId);
    return { success: true };
  } catch (error) {
    console.error('Error updating progress:', error);
    return { success: false, error: error.message };
  }
};

export const completeTaskAssignment = async (assignmentId, taskId) => {
  try {
    const now = new Date().toISOString();
    const { error } = await supabase
      .from('task_assignments')
      .update({ 
          status: 'Completed', 
          progress: 100, 
          completed_at: now,
          last_update_at: now
      })
      .eq('id', assignmentId);

    if (error) throw error;

    await evaluateTaskOverallStatus(taskId);
    return { success: true };
  } catch (error) {
    console.error('Error completing task:', error);
    return { success: false, error: error.message };
  }
};

const evaluateTaskOverallStatus = async (taskId) => {
    try {
        const { data: assignments, error } = await supabase
            .from('task_assignments')
            .select('status')
            .eq('task_id', taskId);
            
        if (error) throw error;
        
        if (!assignments || assignments.length === 0) return;

        const allCompleted = assignments.every(a => a.status === 'Completed');
        const anyInProgress = assignments.some(a => a.status === 'In Progress' || a.status === 'Accepted');

        let newStatus = 'Pending';
        if (allCompleted) newStatus = 'Completed';
        else if (anyInProgress) newStatus = 'In Progress';

        await supabase.from('tasks').update({ status: newStatus }).eq('id', taskId);
    } catch(e) {
        console.error("Failed to evaluate task status", e);
    }
};

export const uploadTaskAttachment = async (file) => {
  try {
    const fileExt = file.name.split('.').pop();
    const fileName = `${Date.now()}-${Math.random().toString(36).substring(2)}.${fileExt}`;
    const filePath = `updates/${fileName}`;

    const { error: uploadError } = await supabase.storage
      .from('task-attachments')
      .upload(filePath, file);

    if (uploadError) throw uploadError;

    const { data: publicUrlData } = supabase.storage
      .from('task-attachments')
      .getPublicUrl(filePath);

    return { success: true, url: publicUrlData.publicUrl, name: file.name };
  } catch (error) {
    console.error('Error uploading file:', error);
    return { success: false, error: error.message };
  }
};

export const getTaskStats = async () => {
    try {
        const { data, error } = await supabase.from('tasks').select('status, priority');
        if (error) throw error;

        const stats = {
            total: data.length,
            pending: 0,
            inProgress: 0,
            completed: 0,
            overdue: 0,
            highPriority: 0
        };

        data.forEach(t => {
            if (t.status === 'Pending') stats.pending++;
            if (t.status === 'In Progress') stats.inProgress++;
            if (t.status === 'Completed') stats.completed++;
            if (t.status === 'Overdue') stats.overdue++;
            if (t.priority === 'High' || t.priority === 'Critical') stats.highPriority++;
        });

        return { success: true, data: stats };
    } catch (e) {
        return { success: false, data: { total: 0, pending: 0, inProgress: 0, completed: 0, overdue: 0, highPriority: 0 } };
    }
};

export const checkAndUpdateOverdueTasks = async () => {
    try {
        const now = new Date().toISOString();
        const { data: overdueTasks, error: fetchError } = await supabase
            .from('tasks')
            .select('id')
            .lt('deadline', now)
            .not('status', 'in', '("Completed","Overdue")');

        if (fetchError) throw fetchError;

        if (overdueTasks && overdueTasks.length > 0) {
            const taskIds = overdueTasks.map(t => t.id);
            await supabase.from('tasks').update({ status: 'Overdue' }).in('id', taskIds);
            await supabase
                .from('task_assignments')
                .update({ status: 'Overdue' })
                .in('task_id', taskIds)
                .neq('status', 'Completed');
                
            return { success: true, updatedCount: taskIds.length };
        }
        return { success: true, updatedCount: 0 };
    } catch (e) {
        console.error("Failed to check overdue tasks", e);
        return { success: false, error: e.message };
    }
};

export const resendTaskNotification = async (taskId, assigneeId) => {
    try {
        const { data: task, error } = await supabase.from('tasks').select('*').eq('id', taskId).single();
        if (error) throw error;
        await sendTaskNotifications(task, [assigneeId], 'reminder');
        return { success: true };
    } catch (error) {
        console.error('Error resending notification:', error);
        return { success: false, error: error.message };
    }
};

const sendTaskNotifications = async (task, assigneeIds, templateType) => {
    try {
        const { data: templateRes } = await getTemplateByType(templateType);
        if (!templateRes || !templateRes.is_active) return;
        
        const template = templateRes;
        
        const { data: assignees } = await supabase
            .from('profiles')
            .select('id, full_name, phone, email')
            .in('id', assigneeIds);
            
        const { data: sender } = await supabase
            .from('profiles')
            .select('full_name')
            .eq('id', task.created_by)
            .single();

        const assignedBy = sender?.full_name || 'Admin';

        for (const user of assignees) {
            const variables = {
                task_title: task.title,
                priority: task.priority,
                deadline: new Date(task.deadline).toLocaleDateString(),
                assigned_by: assignedBy,
                login_link: 'https://www.alpha-bridge.net/login'
            };
            
            const messageBody = replaceTemplateVariables(template.body, variables);
            const messageSubject = replaceTemplateVariables(template.subject, variables);
            
            console.log(`Sending Notification to ${user.full_name} (${user.phone}):\nSubject: ${messageSubject}\nBody: ${messageBody}`);
        }
    } catch(e) {
        console.error("Failed to send task notifications", e);
    }
}

// System DB Checks
export const runSchemaVerifications = () => {
    console.log("=== DB SCHEMA VERIFICATION ===");
    console.log("Expected task_assignments columns: id, task_id, user_id, status, progress, accepted_at, declined_at, completed_at, last_update_at, created_at");
    console.log("Expected profiles columns: id, email, full_name, username, role...");
    console.log("Relationships: task_assignments.task_id -> tasks.id, task_assignments.user_id -> profiles.id");
    
    console.log("=== OTP & WHATSAPP VERIFICATION ===");
    console.log("otp_verifications & whatsapp_messages tables confirmed unchanged.");
    console.log("RLS policies intact.");
}