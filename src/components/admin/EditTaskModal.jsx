import React, { useState, useEffect } from 'react';
import { useToast } from '@/components/ui/use-toast';
import { updateTask } from '@/services/taskService';
import { getTaskCategories } from '@/services/taskCategoryService';
import { supabase } from '@/lib/customSupabaseClient';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { X, Loader2 } from 'lucide-react';

const EditTaskModal = ({ isOpen, onClose, task, onSave }) => {
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    priority: 'Medium',
    category_id: 'none',
    deadline: ''
  });
  const [assignees, setAssignees] = useState([]);
  const [availableUsers, setAvailableUsers] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(false);
  const [selectedUser, setSelectedUser] = useState('');
  const { toast } = useToast();

  useEffect(() => {
    if (isOpen) {
      loadData();
      if (task) {
        setFormData({
          title: task.title || '',
          description: task.description || '',
          priority: task.priority || 'Medium',
          category_id: task.category_id || 'none',
          deadline: task.deadline ? new Date(task.deadline).toISOString().split('T')[0] : ''
        });
        const currentAssignees = task.task_assignments?.map(a => a.profiles) || [];
        setAssignees(currentAssignees.filter(Boolean));
      }
    }
  }, [isOpen, task]);

  const loadData = async () => {
    const { data: usersData } = await supabase.from('profiles').select('id, full_name, email').eq('status', 'active');
    if (usersData) setAvailableUsers(usersData);

    const { data: cats } = await getTaskCategories();
    if (cats) setCategories(cats);
  };

  const handleAddAssignee = () => {
    if (!selectedUser) return;
    const userToAdd = availableUsers.find(u => u.id === selectedUser);
    if (userToAdd && !assignees.find(a => a.id === userToAdd.id)) {
      setAssignees([...assignees, userToAdd]);
    }
    setSelectedUser('');
  };

  const handleRemoveAssignee = (userId) => {
    setAssignees(assignees.filter(a => a.id !== userId));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!formData.title || !formData.deadline) {
      toast({ title: 'Validation Error', description: 'Title and deadline are required.', variant: 'destructive' });
      return;
    }
    
    setLoading(true);
    const assigneeIds = assignees.map(a => a.id);
    const dataToSave = {
        ...formData,
        category_id: formData.category_id === 'none' ? null : formData.category_id
    };

    const res = await updateTask(task.id, dataToSave, assigneeIds);
    setLoading(false);

    if (res.success) {
      toast({ title: 'Task updated successfully' });
      onSave();
      onClose();
    } else {
      toast({ title: 'Error', description: res.error, variant: 'destructive' });
    }
  };

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Edit Task</DialogTitle>
        </DialogHeader>
        
        <form onSubmit={handleSubmit} className="space-y-4 py-4">
          <div className="space-y-2">
            <Label>Title *</Label>
            <Input 
              value={formData.title}
              onChange={(e) => setFormData({...formData, title: e.target.value})}
              placeholder="Task Title"
            />
          </div>

          <div className="space-y-2">
            <Label>Description</Label>
            <Textarea 
              value={formData.description}
              onChange={(e) => setFormData({...formData, description: e.target.value})}
              placeholder="Task Details"
              rows={3}
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label>Priority *</Label>
              <Select value={formData.priority} onValueChange={(v) => setFormData({...formData, priority: v})}>
                <SelectTrigger>
                  <SelectValue placeholder="Priority" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="Low">Low</SelectItem>
                  <SelectItem value="Medium">Medium</SelectItem>
                  <SelectItem value="High">High</SelectItem>
                  <SelectItem value="Critical">Critical</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label>Category</Label>
              <Select value={formData.category_id} onValueChange={(v) => setFormData({...formData, category_id: v})}>
                <SelectTrigger>
                  <SelectValue placeholder="Category" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="none">No Category</SelectItem>
                  {categories.map(cat => (
                    <SelectItem key={cat.id} value={cat.id}>{cat.name}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </div>

          <div className="space-y-2">
            <Label>Deadline *</Label>
            <Input 
              type="date"
              value={formData.deadline}
              onChange={(e) => setFormData({...formData, deadline: e.target.value})}
            />
          </div>

          <div className="space-y-2">
            <Label>Assignees</Label>
            <div className="flex gap-2">
              <Select value={selectedUser} onValueChange={setSelectedUser}>
                <SelectTrigger className="flex-1">
                  <SelectValue placeholder="Select user to assign" />
                </SelectTrigger>
                <SelectContent>
                  {availableUsers.filter(u => !assignees.find(a => a.id === u.id)).map(user => (
                    <SelectItem key={user.id} value={user.id}>{user.full_name} ({user.email})</SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <Button type="button" onClick={handleAddAssignee} variant="secondary">Add</Button>
            </div>
            {assignees.length > 0 && (
              <div className="flex flex-wrap gap-2 mt-3 p-3 border rounded-md bg-gray-50">
                {assignees.map(user => (
                  <Badge key={user.id} variant="outline" className="bg-white px-2 py-1 flex items-center gap-1">
                    {user.full_name}
                    <X className="w-3 h-3 cursor-pointer hover:text-red-500" onClick={() => handleRemoveAssignee(user.id)} />
                  </Badge>
                ))}
              </div>
            )}
          </div>

          <DialogFooter className="mt-6">
            <Button type="button" variant="outline" onClick={onClose} disabled={loading}>Cancel</Button>
            <Button type="submit" className="bg-[#003D82]" disabled={loading}>
              {loading && <Loader2 className="w-4 h-4 mr-2 animate-spin" />}
              Save Changes
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
};

export default EditTaskModal;