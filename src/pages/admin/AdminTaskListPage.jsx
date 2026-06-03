import React, { useState, useEffect } from 'react';
import { useToast } from '@/components/ui/use-toast';
import { getTasks, deleteTask, resendTaskNotification } from '@/services/taskService';
import { getTaskCategories } from '@/services/taskCategoryService';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Loader2, Plus, Edit, Trash2, Send, FilterX } from 'lucide-react';
import { format } from 'date-fns';
import { Link } from 'react-router-dom';
import EditTaskModal from '@/components/admin/EditTaskModal';
import { getPriorityColor, getStatusColor } from '@/components/admin/TaskDashboardCard';

const AdminTaskListPage = () => {
  const [tasks, setTasks] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState({ status: 'All', priority: 'All', category: 'All' });
  
  const [editTask, setEditTask] = useState(null);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  
  const { toast } = useToast();

  useEffect(() => {
    loadCategories();
  }, []);

  useEffect(() => {
    loadTasks();
  }, [filters]);

  const loadCategories = async () => {
    const res = await getTaskCategories();
    if (res.success) setCategories(res.data);
  };

  const loadTasks = async () => {
    setLoading(true);
    const res = await getTasks(filters);
    if (res.success) {
      setTasks(res.data);
    } else {
      toast({ title: "Error loading tasks", description: res.error, variant: "destructive" });
    }
    setLoading(false);
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this task?')) return;
    const res = await deleteTask(id);
    if (res.success) {
      toast({ title: "Task deleted successfully" });
      loadTasks();
    } else {
      toast({ title: "Error deleting task", description: res.error, variant: "destructive" });
    }
  };

  const handleResendNotification = async (taskId, assigneeId) => {
    const res = await resendTaskNotification(taskId, assigneeId);
    if (res.success) {
      toast({ title: "Notification sent successfully" });
    } else {
      toast({ title: "Failed to send notification", description: res.error, variant: "destructive" });
    }
  };

  const openEditModal = (task) => {
    setEditTask(task);
    setIsEditModalOpen(true);
  };

  return (
    <div className="max-w-7xl mx-auto space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-[#003D82]">Task Management</h1>
          <p className="text-gray-500">View and manage all system tasks.</p>
        </div>
        <Link to="/admin/tasks/create">
          <Button className="bg-[#003D82]"><Plus className="w-4 h-4 mr-2" /> Create Task</Button>
        </Link>
      </div>

      <div className="bg-white p-4 rounded-lg shadow-sm border flex flex-wrap items-center gap-4">
        <span className="text-sm font-medium text-gray-700">Filters:</span>
        <Select value={filters.status} onValueChange={v => setFilters({...filters, status: v})}>
          <SelectTrigger className="w-[140px]"><SelectValue placeholder="Status" /></SelectTrigger>
          <SelectContent>
            <SelectItem value="All">All Statuses</SelectItem>
            <SelectItem value="Pending">Pending</SelectItem>
            <SelectItem value="In Progress">In Progress</SelectItem>
            <SelectItem value="Completed">Completed</SelectItem>
            <SelectItem value="Overdue">Overdue</SelectItem>
          </SelectContent>
        </Select>

        <Select value={filters.priority} onValueChange={v => setFilters({...filters, priority: v})}>
          <SelectTrigger className="w-[140px]"><SelectValue placeholder="Priority" /></SelectTrigger>
          <SelectContent>
            <SelectItem value="All">All Priorities</SelectItem>
            <SelectItem value="Low">Low</SelectItem>
            <SelectItem value="Medium">Medium</SelectItem>
            <SelectItem value="High">High</SelectItem>
            <SelectItem value="Critical">Critical</SelectItem>
          </SelectContent>
        </Select>

        <Select value={filters.category} onValueChange={v => setFilters({...filters, category: v})}>
          <SelectTrigger className="w-[160px]"><SelectValue placeholder="Category" /></SelectTrigger>
          <SelectContent>
            <SelectItem value="All">All Categories</SelectItem>
            {categories.map(c => <SelectItem key={c.id} value={c.id}>{c.name}</SelectItem>)}
          </SelectContent>
        </Select>

        <Button variant="ghost" onClick={() => setFilters({status: 'All', priority: 'All', category: 'All'})}>
          <FilterX className="w-4 h-4 mr-2" /> Clear
        </Button>
      </div>

      <div className="bg-white rounded-lg shadow border overflow-hidden">
        {loading ? (
          <div className="flex justify-center p-10"><Loader2 className="w-8 h-8 animate-spin text-[#003D82]" /></div>
        ) : tasks.length === 0 ? (
          <div className="p-10 text-center text-gray-500">No tasks found matching your criteria.</div>
        ) : (
          <Table>
            <TableHeader className="bg-gray-50">
              <TableRow>
                <TableHead>Title</TableHead>
                <TableHead>Category</TableHead>
                <TableHead>Priority</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Deadline</TableHead>
                <TableHead>Assignees</TableHead>
                <TableHead className="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {tasks.map(task => (
                <TableRow key={task.id}>
                  <TableCell className="font-medium">{task.title}</TableCell>
                  <TableCell>
                    {task.task_categories ? (
                      <Badge variant="outline" style={{borderColor: task.task_categories.color, color: task.task_categories.color}}>
                        {task.task_categories.name}
                      </Badge>
                    ) : '-'}
                  </TableCell>
                  <TableCell><Badge className={getPriorityColor(task.priority)}>{task.priority}</Badge></TableCell>
                  <TableCell><Badge className={getStatusColor(task.status)}>{task.status}</Badge></TableCell>
                  <TableCell>{task.deadline ? format(new Date(task.deadline), 'MMM dd, yyyy') : '-'}</TableCell>
                  <TableCell>
                    <div className="flex flex-col gap-1">
                      {task.task_assignments?.map(a => (
                         <div key={a.id} className="text-xs flex items-center justify-between gap-2 bg-gray-50 p-1 rounded">
                           <span className="truncate max-w-[100px]" title={a.profiles?.full_name}>{a.profiles?.full_name?.split(' ')[0]}</span>
                           <Button variant="ghost" size="icon" className="h-5 w-5" title="Remind" onClick={() => handleResendNotification(task.id, a.user_id)}>
                             <Send className="h-3 w-3 text-blue-500" />
                           </Button>
                         </div>
                      ))}
                    </div>
                  </TableCell>
                  <TableCell className="text-right">
                    <Button variant="ghost" size="icon" onClick={() => openEditModal(task)}>
                      <Edit className="w-4 h-4 text-gray-600" />
                    </Button>
                    <Button variant="ghost" size="icon" onClick={() => handleDelete(task.id)}>
                      <Trash2 className="w-4 h-4 text-red-500" />
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        )}
      </div>

      <EditTaskModal 
        isOpen={isEditModalOpen} 
        onClose={() => setIsEditModalOpen(false)} 
        task={editTask}
        onSave={loadTasks}
      />
    </div>
  );
};

export default AdminTaskListPage;