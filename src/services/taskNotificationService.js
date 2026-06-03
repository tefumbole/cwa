import { sendWhatsAppMessage } from './wasenderapiService';

/**
 * Task Notification Service
 * Handles sending WhatsApp notifications for task events.
 */

export const sendTaskAssignmentNotification = async (assigneePhone, assigneeName, taskTitle, deadline) => {
    if (!assigneePhone) return { success: false, error: "No phone number provided" };
    
    const message = `Hello ${assigneeName},\n\nYou have been assigned a new task:\n*${taskTitle}*\n\nDeadline: ${new Date(deadline).toLocaleDateString()}\n\nPlease log in to your dashboard to review and accept this task.`;
    
    return await sendWhatsAppMessage(assigneePhone, message);
};

export const sendTaskReminderNotification = async (assigneePhone, assigneeName, taskTitle, deadline) => {
    if (!assigneePhone) return { success: false, error: "No phone number provided" };
    
    const message = `*Reminder* ⏰\n\nHello ${assigneeName},\nThis is a reminder for your pending task:\n*${taskTitle}*\n\nDeadline: ${new Date(deadline).toLocaleDateString()}\n\nPlease update your progress on the dashboard.`;
    
    return await sendWhatsAppMessage(assigneePhone, message);
};

export const sendAdminTaskAcceptedNotification = async (adminPhone, assigneeName, taskTitle) => {
    if (!adminPhone) return { success: false, error: "No phone number provided" };
    
    const message = `Task Update 📊\n\n${assigneeName} has *accepted* the task:\n*${taskTitle}*`;
    
    return await sendWhatsAppMessage(adminPhone, message);
};

export const sendAdminTaskCompletedNotification = async (adminPhone, assigneeName, taskTitle) => {
    if (!adminPhone) return { success: false, error: "No phone number provided" };
    
    const message = `Task Completed ✅\n\n${assigneeName} has completed their assignment for:\n*${taskTitle}*`;
    
    return await sendWhatsAppMessage(adminPhone, message);
};