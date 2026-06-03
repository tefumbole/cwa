import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { useToast } from '@/components/ui/use-toast';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { createTaskWithAssignments } from '@/services/taskService';
import { getAllUsersForAssignment } from '@/services/userService';
import { sendTaskAssignmentNotification } from '@/services/taskNotificationService';
import { Loader2, Save, Users, AlertCircle, X, Search } from 'lucide-react';

const DRAFT_KEY = 'task_draft_new';

const CreateTaskPage = () => {
  const navigate = useNavigate();
  const { toast } = useToast();
  const dropdownRef = useRef(null);

  const [loading, setLoading] = useState(false);
  const [users, setUsers] = useState([]);
  
  // User Selection State
  const [selectedUsers, setSelectedUsers] = useState([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [showUserDropdown, setShowUserDropdown] = useState(false);
  
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    priority: 'Medium',
    start_date: '',
    deadline: '',
  });

  useEffect(() => {
    fetchUsers();
    const draft = localStorage.getItem(DRAFT_KEY);
    if (draft) {
      try { 
        const parsed = JSON.parse(draft);
        setFormData(parsed.formData);
        setSelectedUsers(parsed.selectedUsers || []);
      } catch(e) {}
    }

    const handleClickOutside = (event) => {
        if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
            setShowUserDropdown(false);
        }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  // Auto-save draft
  useEffect(() => {
    const timer = setTimeout(() => {
      localStorage.setItem(DRAFT_KEY, JSON.stringify({formData, selectedUsers}));
    }, 1000);
    return () => clearTimeout(timer);
  }, [formData, selectedUsers]);

  const fetchUsers = async () => {
    const res = await getAllUsersForAssignment();
    if (res.success) {
        setUsers(res.data);
    } else {
        toast({ title: "Failed to load users", description: res.error, variant: "destructive" });
    }
  };

  const handleAddUser = (user) => {
      if (!selectedUsers.find(u => u.id === user.id)) {
          setSelectedUsers([...selectedUsers, user]);
      }
      setSearchQuery('');
      setShowUserDropdown(false);
  };

  const handleRemoveUser = (userId) => {
      setSelectedUsers(selectedUsers.filter(u => u.id !== userId));
  };

  const filteredUsers = users.filter(user => 
      !selectedUsers.find(su => su.id === user.id) && 
      ((user.name || '').toLowerCase().includes(searchQuery.toLowerCase()) || 
       (user.email || '').toLowerCase().includes(searchQuery.toLowerCase()))
  );

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!formData.title.trim()) return toast({ title: "Required", description: "Title is required", variant: "destructive" });
    if (!formData.deadline) return toast({ title: "Required", description: "Deadline is required", variant: "destructive" });
    if (selectedUsers.length === 0) return toast({ title: "Required", description: "Assign at least one user", variant: "destructive" });

    setLoading(true);
    const assigneeIds = selectedUsers.map(u => u.id);
    
    const res = await createTaskWithAssignments(formData, assigneeIds);
    
    if (res.success) {
      toast({ title: "Task Created Successfully" });
      localStorage.removeItem(DRAFT_KEY);
      
      selectedUsers.forEach(u => {
          if (u.phone) {
              sendTaskAssignmentNotification(u.phone, u.name || u.email, formData.title, formData.deadline)
                  .catch(err => console.error("Notification failed", err));
          }
      });

      navigate('/admin/tasks/dashboard');
    } else {
      const errMsg = res.error?.includes('row-level security') 
        ? 'Permission Denied: You do not have the required role to create task assignments.'
        : res.error;
      toast({ title: "Failed to create task", description: errMsg, variant: "destructive" });
    }
    setLoading(false);
  };

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-[#003D82]">Create New Task</h1>
        <p className="text-gray-500">Assign tasks and track team progress.</p>
      </div>

      <form onSubmit={handleSubmit}>
        <Card className="shadow-md">
          <CardHeader className="bg-gray-50/50 border-b">
            <CardTitle>Task Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-6 p-6">
            <div className="space-y-2">
              <Label htmlFor="title" className="font-semibold">Task Title <span className="text-red-500">*</span></Label>
              <Input 
                id="title" 
                placeholder="e.g. Q3 Financial Report Review" 
                value={formData.title}
                onChange={e => setFormData({...formData, title: e.target.value})}
                className="bg-white text-gray-900 border-gray-300 focus:border-[#003D82] focus:ring-[#003D82]"
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="description" className="font-semibold">Description</Label>
              <Textarea 
                id="description" 
                placeholder="Provide detailed instructions..." 
                className="min-h-[120px] bg-white text-gray-900 border-gray-300 focus:border-[#003D82] focus:ring-[#003D82]"
                value={formData.description}
                onChange={e => setFormData({...formData, description: e.target.value})}
              />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="space-y-2">
                <Label className="font-semibold">Priority</Label>
                <Select value={formData.priority} onValueChange={v => setFormData({...formData, priority: v})}>
                  <SelectTrigger className="bg-white text-gray-900 border-gray-300 focus:border-[#003D82] focus:ring-[#003D82]">
                    <SelectValue placeholder="Select priority" />
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
                <Label htmlFor="start" className="font-semibold">Start Date</Label>
                <Input 
                  id="start" 
                  type="date" 
                  value={formData.start_date}
                  onChange={e => setFormData({...formData, start_date: e.target.value})}
                  className="bg-white text-gray-900 border-gray-300 focus:border-[#003D82] focus:ring-[#003D82]"
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="deadline" className="font-semibold">Deadline <span className="text-red-500">*</span></Label>
                <Input 
                  id="deadline" 
                  type="date" 
                  value={formData.deadline}
                  onChange={e => setFormData({...formData, deadline: e.target.value})}
                  className="bg-white text-gray-900 border-gray-300 focus:border-[#003D82] focus:ring-[#003D82]"
                  min={new Date().toISOString().split('T')[0]}
                />
              </div>
            </div>

            <div className="space-y-3 pt-4 border-t">
              <Label className="flex items-center text-base font-semibold">
                <Users className="w-5 h-5 mr-2 text-[#003D82]" /> Assign To <span className="text-red-500 ml-1">*</span>
              </Label>
              
              {selectedUsers.length > 0 && (
                  <div className="flex flex-wrap gap-2 mb-3">
                      {selectedUsers.map(user => (
                          <Badge key={user.id} variant="secondary" className="px-3 py-1.5 bg-blue-50 text-[#003D82] border-blue-200 text-sm flex items-center gap-2">
                              {user.name || user.email}
                              <button 
                                  type="button" 
                                  onClick={() => handleRemoveUser(user.id)}
                                  className="hover:bg-blue-200 rounded-full p-0.5 transition-colors"
                              >
                                  <X className="w-3 h-3" />
                              </button>
                          </Badge>
                      ))}
                  </div>
              )}

              <div className="relative" ref={dropdownRef}>
                  <div className="relative">
                      <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                      <Input
                          type="text"
                          placeholder="Search for users by name or email..."
                          value={searchQuery}
                          onChange={(e) => {
                              setSearchQuery(e.target.value);
                              setShowUserDropdown(true);
                          }}
                          onFocus={() => setShowUserDropdown(true)}
                          className="pl-9 bg-white text-gray-900 border-gray-300 focus:border-[#003D82] focus:ring-[#003D82]"
                      />
                  </div>

                  {showUserDropdown && (
                      <div className="absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto">
                          {users.length === 0 ? (
                              <div className="p-4 text-center text-gray-500 text-sm flex items-center justify-center gap-2">
                                  <Loader2 className="w-4 h-4 animate-spin" /> Loading users...
                              </div>
                          ) : filteredUsers.length === 0 ? (
                              <div className="p-4 text-center text-gray-500 text-sm">No users found.</div>
                          ) : (
                              filteredUsers.map(user => (
                                  <div 
                                      key={user.id}
                                      className="px-4 py-2 hover:bg-gray-50 cursor-pointer border-b last:border-b-0 flex justify-between items-center"
                                      onClick={() => handleAddUser(user)}
                                  >
                                      <div>
                                          <p className="font-medium text-gray-900">{user.name || 'Unnamed'}</p>
                                          <p className="text-xs text-gray-500">{user.email}</p>
                                      </div>
                                      <Badge variant="outline" className="text-[10px] capitalize">
                                          {user.role}
                                      </Badge>
                                  </div>
                              ))
                          )}
                      </div>
                  )}
              </div>
              {selectedUsers.length === 0 && (
                  <p className="text-xs text-red-500 flex items-center mt-1">
                      <AlertCircle className="w-3 h-3 mr-1" /> Please select at least one assignee.
                  </p>
              )}
            </div>

            <div className="flex justify-end gap-4 pt-6 border-t mt-6">
              <Button type="button" variant="outline" onClick={() => navigate(-1)} className="px-6">Cancel</Button>
              <Button type="submit" disabled={loading || selectedUsers.length === 0} className="bg-[#003D82] hover:bg-[#002a5a] text-white px-8 shadow-md">
                {loading ? <Loader2 className="w-4 h-4 mr-2 animate-spin" /> : <Save className="w-4 h-4 mr-2" />}
                Create & Assign Task
              </Button>
            </div>
          </CardContent>
        </Card>
      </form>
    </div>
  );
};

export default CreateTaskPage;