import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { useToast } from '@/components/ui/use-toast';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { createBatchTasksWithAssignments } from '@/services/taskService';
import { searchUsersForTaskAssignment } from '@/services/userService';
import QuickAssigneeDialog from '@/components/admin/QuickAssigneeDialog';
import {
  Loader2,
  Save,
  Users,
  AlertCircle,
  X,
  Search,
  Paperclip,
  Calendar,
  Plus,
  Trash2,
  UserPlus,
  Send,
  Clock,
} from 'lucide-react';
import { DEFAULT_TASK_NOTIFICATION_TEMPLATE } from '@/utils/taskPersonalization';

const DRAFT_KEY = 'task_draft_new_v2';

const emptyTaskRow = () => ({ subject: '', description: '' });

const CreateTaskPage = () => {
  const navigate = useNavigate();
  const { toast } = useToast();
  const dropdownRef = useRef(null);
  const fileInputRef = useRef(null);

  const [loading, setLoading] = useState(false);
  const [searchResults, setSearchResults] = useState([]);
  const [searchLoading, setSearchLoading] = useState(false);
  const [selectedUsers, setSelectedUsers] = useState([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [assigneeTab, setAssigneeTab] = useState('all');
  const [showUserDropdown, setShowUserDropdown] = useState(false);
  const [showNewUserDialog, setShowNewUserDialog] = useState(false);
  const [sourceFiles, setSourceFiles] = useState([]);
  const [sendMode, setSendMode] = useState('now');
  const [scheduleTimes, setScheduleTimes] = useState(['']);
  const [notificationTemplate] = useState(DEFAULT_TASK_NOTIFICATION_TEMPLATE);

  const [taskRows, setTaskRows] = useState([emptyTaskRow()]);
  const [dates, setDates] = useState({ start_date: '', deadline: '', priority: 'Medium' });

  useEffect(() => {
    const draft = localStorage.getItem(DRAFT_KEY);
    if (draft) {
      try {
        const parsed = JSON.parse(draft);
        setTaskRows(parsed.taskRows?.length ? parsed.taskRows : [emptyTaskRow()]);
        setDates(parsed.dates || dates);
        setSelectedUsers(parsed.selectedUsers || []);
        setSendMode(parsed.sendMode || 'now');
        setScheduleTimes(parsed.scheduleTimes || ['']);
      } catch {
        /* ignore */
      }
    }

    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setShowUserDropdown(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  useEffect(() => {
    const timer = setTimeout(() => {
      localStorage.setItem(
        DRAFT_KEY,
        JSON.stringify({ taskRows, dates, selectedUsers, sendMode, scheduleTimes })
      );
    }, 1000);
    return () => clearTimeout(timer);
  }, [taskRows, dates, selectedUsers, sendMode, scheduleTimes]);

  useEffect(() => {
    const timer = setTimeout(async () => {
      setSearchLoading(true);
      const res = await searchUsersForTaskAssignment(searchQuery, assigneeTab);
      setSearchResults(res.success ? res.data || [] : []);
      setSearchLoading(false);
    }, searchQuery.trim() ? 250 : 0);
    return () => clearTimeout(timer);
  }, [searchQuery, assigneeTab]);

  const handleAddUser = (user) => {
    if (!selectedUsers.find((u) => u.id === user.id)) {
      setSelectedUsers((prev) => [...prev, user]);
    }
    setSearchQuery('');
    setShowUserDropdown(false);
  };

  const handleAddAllVisible = () => {
    const toAdd = filteredUsers.filter((u) => !selectedUsers.find((su) => su.id === u.id));
    if (!toAdd.length) return;
    setSelectedUsers((prev) => [...prev, ...toAdd]);
    setSearchQuery('');
    setShowUserDropdown(false);
    toast({ title: `${toAdd.length} recipient(s) added` });
  };

  const handleRemoveUser = (userId) => {
    setSelectedUsers((prev) => prev.filter((u) => u.id !== userId));
  };

  const filteredUsers = searchResults.filter(
    (user) =>
      !selectedUsers.find((su) => su.id === user.id) &&
      (!searchQuery.trim()
        || (user.name || user.full_name || '').toLowerCase().includes(searchQuery.toLowerCase())
        || (user.email || '').toLowerCase().includes(searchQuery.toLowerCase())
        || (user.phone || '').includes(searchQuery))
  );

  const updateTaskRow = (index, field, value) => {
    setTaskRows((prev) => prev.map((row, i) => (i === index ? { ...row, [field]: value } : row)));
  };

  const addTaskRow = () => setTaskRows((prev) => [...prev, emptyTaskRow()]);

  const removeTaskRow = (index) => {
    if (taskRows.length === 1) return;
    setTaskRows((prev) => prev.filter((_, i) => i !== index));
  };

  const handlePdfChange = (e) => {
    const files = Array.from(e.target.files || []);
    const pdfs = files.filter((f) => f.type === 'application/pdf' || f.name.toLowerCase().endsWith('.pdf'));
    if (pdfs.length !== files.length) {
      toast({ title: 'PDF only', description: 'Only PDF files are attached to tasks.', variant: 'destructive' });
    }
    if (pdfs.length) setSourceFiles((prev) => [...prev, ...pdfs]);
    e.target.value = '';
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    const validTasks = taskRows.filter((row) => row.subject.trim());
    if (!validTasks.length) {
      toast({ title: 'Required', description: 'Add at least one task with a subject.', variant: 'destructive' });
      return;
    }
    if (!dates.deadline) {
      toast({ title: 'Required', description: 'End date is required.', variant: 'destructive' });
      return;
    }
    if (selectedUsers.length === 0) {
      toast({ title: 'Required', description: 'Select or create at least one assignee.', variant: 'destructive' });
      return;
    }
    if (sendMode === 'schedule' && !scheduleTimes.some((t) => t.trim())) {
      toast({ title: 'Required', description: 'Pick at least one send date/time.', variant: 'destructive' });
      return;
    }

    setLoading(true);
    const assigneeIds = selectedUsers.map((u) => u.id);
    const scheduleLater = sendMode === 'schedule';
    const schedules = scheduleLater
      ? scheduleTimes.filter((t) => t.trim()).map((t) => new Date(t).toISOString())
      : [];

    const tasksPayload = validTasks.map((row) => ({
      title: row.subject.trim(),
      description: row.description.trim(),
      priority: dates.priority,
      start_date: dates.start_date || null,
      deadline: dates.deadline,
    }));

    const res = await createBatchTasksWithAssignments(tasksPayload, assigneeIds, {
      notificationTemplate,
      sourceFiles,
      schedules,
      scheduleLater,
    });

    if (res.success || res.count > 0) {
      toast({
        title: scheduleLater ? 'Tasks scheduled' : 'Tasks sent',
        description: scheduleLater
          ? `${res.count} task(s) scheduled. Assignees will be notified at the chosen time(s).`
          : `${res.count} task(s) created. Personalized WhatsApp messages sent with accept links.`,
      });
      localStorage.removeItem(DRAFT_KEY);
      navigate('/admin/tasks/dashboard');
    } else {
      toast({ title: 'Failed to create tasks', description: res.error, variant: 'destructive' });
    }
    setLoading(false);
  };

  return (
    <div className="max-w-4xl mx-auto space-y-6 pb-10">
      <div>
        <h1 className="text-3xl font-bold text-[#003D82]">Create Task</h1>
        <p className="text-gray-500">
          Add one or more tasks, choose recipients, attach a PDF, and send immediately or on schedule.
        </p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        {/* Step 1: Tasks */}
        <Card>
          <CardHeader>
            <CardTitle>1. Task Details</CardTitle>
            <CardDescription>Subject and description for each task. Add multiple tasks to send them all at once.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            {taskRows.map((row, index) => (
              <div key={`task-row-${index}`} className="rounded-xl border bg-slate-50/50 p-4 space-y-3">
                <div className="flex items-center justify-between">
                  <span className="text-sm font-semibold text-[#003D82]">Task {index + 1}</span>
                  {taskRows.length > 1 && (
                    <Button type="button" variant="ghost" size="sm" className="text-rose-600" onClick={() => removeTaskRow(index)}>
                      <Trash2 className="w-4 h-4 mr-1" /> Remove
                    </Button>
                  )}
                </div>
                <div className="space-y-2">
                  <Label>Subject *</Label>
                  <Input
                    value={row.subject}
                    onChange={(e) => updateTaskRow(index, 'subject', e.target.value)}
                    placeholder="e.g. Software Testing Report"
                  />
                </div>
                <div className="space-y-2">
                  <Label>Description</Label>
                  <Textarea
                    rows={4}
                    value={row.description}
                    onChange={(e) => updateTaskRow(index, 'description', e.target.value)}
                    placeholder="Detailed instructions for the assignee..."
                  />
                </div>
              </div>
            ))}
            <Button type="button" variant="outline" onClick={addTaskRow} className="w-full border-dashed">
              <Plus className="w-4 h-4 mr-2" /> Add Another Task
            </Button>
          </CardContent>
        </Card>

        {/* Step 2: Assignees */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Users className="w-5 h-5" /> 2. Assign To *
            </CardTitle>
            <CardDescription>Select staff, customers, or a group. Create a new user if they are not in the system yet.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex flex-wrap gap-2">
              {[
                { id: 'all', label: 'All' },
                { id: 'staff', label: 'Staff' },
                { id: 'customers', label: 'Customers & Members' },
              ].map((tab) => (
                <button
                  key={tab.id}
                  type="button"
                  onClick={() => setAssigneeTab(tab.id)}
                  className={`rounded-full px-3 py-1 text-xs font-medium border ${
                    assigneeTab === tab.id
                      ? 'bg-[#003D82] text-white border-[#003D82]'
                      : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'
                  }`}
                >
                  {tab.label}
                </button>
              ))}
              <Button type="button" variant="outline" size="sm" onClick={() => setShowNewUserDialog(true)}>
                <UserPlus className="w-4 h-4 mr-1" /> Create New User
              </Button>
            </div>

            {selectedUsers.length > 0 && (
              <div className="flex flex-wrap gap-2">
                {selectedUsers.map((user) => (
                  <Badge key={user.id} variant="secondary" className="px-3 py-1.5 bg-blue-50 text-[#003D82] border-blue-200 flex items-center gap-2">
                    {user.name || user.full_name || user.email}
                    <button type="button" onClick={() => handleRemoveUser(user.id)}><X className="w-3 h-3" /></button>
                  </Badge>
                ))}
              </div>
            )}

            <div className="relative" ref={dropdownRef}>
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
              <Input
                className="pl-9"
                placeholder="Search by name, email, or phone..."
                value={searchQuery}
                onChange={(e) => { setSearchQuery(e.target.value); setShowUserDropdown(true); }}
                onFocus={() => setShowUserDropdown(true)}
              />
              {showUserDropdown && (
                <div className="absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto">
                  {filteredUsers.length > 1 && (
                    <button
                      type="button"
                      className="w-full px-4 py-2 text-left text-xs font-medium text-[#003D82] bg-blue-50 border-b hover:bg-blue-100"
                      onClick={handleAddAllVisible}
                    >
                      Add all {filteredUsers.length} visible results
                    </button>
                  )}
                  {searchLoading ? (
                    <div className="p-4 text-center text-gray-500 text-sm flex items-center justify-center gap-2">
                      <Loader2 className="w-4 h-4 animate-spin" /> Searching...
                    </div>
                  ) : filteredUsers.length === 0 ? (
                    <div className="p-4 text-center text-gray-500 text-sm">
                      No users found.
                      <button type="button" className="block mx-auto mt-2 text-[#003D82] underline" onClick={() => setShowNewUserDialog(true)}>
                        Create new user
                      </button>
                    </div>
                  ) : (
                    filteredUsers.map((user) => (
                      <div
                        key={user.id}
                        className="px-4 py-2 hover:bg-gray-50 cursor-pointer border-b last:border-b-0"
                        onClick={() => handleAddUser(user)}
                      >
                        <p className="font-medium">{user.name || user.full_name || 'Unnamed'}</p>
                        <p className="text-xs text-gray-500">
                          {user.email}{user.phone ? ` · ${user.phone}` : ''}{user.role ? ` · ${user.role}` : ''}
                        </p>
                      </div>
                    ))
                  )}
                </div>
              )}
            </div>
            {selectedUsers.length === 0 && (
              <p className="text-xs text-red-500 flex items-center">
                <AlertCircle className="w-3 h-3 mr-1" /> Select at least one assignee or create a new user.
              </p>
            )}
          </CardContent>
        </Card>

        {/* Step 3: Dates */}
        <Card>
          <CardHeader>
            <CardTitle>3. Task Period</CardTitle>
          </CardHeader>
          <CardContent className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="space-y-2">
              <Label>Start Date</Label>
              <Input type="date" value={dates.start_date} onChange={(e) => setDates({ ...dates, start_date: e.target.value })} />
            </div>
            <div className="space-y-2">
              <Label>End Date *</Label>
              <Input
                type="date"
                value={dates.deadline}
                min={dates.start_date || new Date().toISOString().split('T')[0]}
                onChange={(e) => setDates({ ...dates, deadline: e.target.value })}
              />
            </div>
            <div className="space-y-2">
              <Label>Priority</Label>
              <select
                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                value={dates.priority}
                onChange={(e) => setDates({ ...dates, priority: e.target.value })}
              >
                {['Low', 'Medium', 'High', 'Critical'].map((p) => (
                  <option key={p} value={p}>{p}</option>
                ))}
              </select>
            </div>
          </CardContent>
        </Card>

        {/* Step 4: PDF */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><Paperclip className="w-5 h-5" /> 4. Attach PDF (optional)</CardTitle>
          </CardHeader>
          <CardContent>
            {sourceFiles.length > 0 && (
              <ul className="mb-3 space-y-1 text-sm">
                {sourceFiles.map((file, index) => (
                  <li key={`${file.name}-${index}`} className="flex justify-between rounded bg-slate-50 px-3 py-2">
                    <span className="truncate">{file.name}</span>
                    <button type="button" className="text-xs text-rose-600" onClick={() => setSourceFiles((prev) => prev.filter((_, i) => i !== index))}>Remove</button>
                  </li>
                ))}
              </ul>
            )}
            <input ref={fileInputRef} type="file" accept=".pdf,application/pdf" multiple className="hidden" onChange={handlePdfChange} />
            <Button type="button" variant="outline" onClick={() => fileInputRef.current?.click()}>
              <Paperclip className="w-4 h-4 mr-2" /> Browse PDF
            </Button>
          </CardContent>
        </Card>

        {/* Step 5: Schedule */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><Clock className="w-5 h-5" /> 5. When to Send</CardTitle>
            <CardDescription>Send WhatsApp notifications immediately or schedule for later.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <RadioGroup value={sendMode} onValueChange={setSendMode} className="space-y-2">
              <label className="flex items-center gap-3 rounded-lg border p-3 cursor-pointer hover:bg-slate-50">
                <RadioGroupItem value="now" />
                <div>
                  <p className="font-medium flex items-center gap-2"><Send className="w-4 h-4" /> Send immediately</p>
                  <p className="text-xs text-gray-500">Each assignee gets a personalized message with an accept link.</p>
                </div>
              </label>
              <label className="flex items-center gap-3 rounded-lg border p-3 cursor-pointer hover:bg-slate-50">
                <RadioGroupItem value="schedule" />
                <div>
                  <p className="font-medium flex items-center gap-2"><Calendar className="w-4 h-4" /> Schedule for later</p>
                  <p className="text-xs text-gray-500">Notifications go out at the date and time you choose.</p>
                </div>
              </label>
            </RadioGroup>

            {sendMode === 'schedule' && (
              <div className="space-y-2 pl-1">
                {scheduleTimes.map((time, index) => (
                  <div key={`sched-${index}`} className="flex gap-2">
                    <Input
                      type="datetime-local"
                      value={time}
                      onChange={(e) => setScheduleTimes((prev) => prev.map((t, i) => (i === index ? e.target.value : t)))}
                    />
                    {scheduleTimes.length > 1 && (
                      <Button type="button" variant="ghost" className="text-rose-600" onClick={() => setScheduleTimes((prev) => prev.filter((_, i) => i !== index))}>Remove</Button>
                    )}
                  </div>
                ))}
                <Button type="button" variant="link" className="px-0" onClick={() => setScheduleTimes((prev) => [...prev, ''])}>
                  <Plus className="w-4 h-4 mr-1" /> Add another schedule
                </Button>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Message preview */}
        <Card className="bg-blue-50/40 border-blue-100">
          <CardHeader>
            <CardTitle className="text-base">Message Preview</CardTitle>
            <CardDescription>Each recipient receives this format with their name, subject, and description filled in.</CardDescription>
          </CardHeader>
          <CardContent>
            <pre className="whitespace-pre-wrap text-xs font-mono text-gray-700 bg-white rounded-lg p-4 border">{notificationTemplate}</pre>
          </CardContent>
        </Card>

        <div className="flex flex-col sm:flex-row gap-3 justify-end">
          <Button type="button" variant="outline" onClick={() => navigate(-1)}>Cancel</Button>
          <Button type="submit" disabled={loading || selectedUsers.length === 0} className="bg-[#003D82] hover:bg-[#002a5a] min-w-[200px]">
            {loading ? <Loader2 className="w-4 h-4 mr-2 animate-spin" /> : <Save className="w-4 h-4 mr-2" />}
            {sendMode === 'schedule' ? 'Create & Schedule All' : 'Create & Send All'}
          </Button>
        </div>
      </form>

      <QuickAssigneeDialog
        open={showNewUserDialog}
        onOpenChange={setShowNewUserDialog}
        onCreated={handleAddUser}
      />
    </div>
  );
};

export default CreateTaskPage;
