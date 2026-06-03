import React, { useState, useEffect } from 'react';
import { useToast } from '@/components/ui/use-toast';
import { getMyTasks, acceptTaskAssignment, declineTaskAssignment } from '@/services/taskService';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Loader2, Calendar, Clock, AlertCircle, User, Check, X } from 'lucide-react';
import { format } from 'date-fns';

export const getPriorityColor = (priority) => {
    switch(priority) {
        case 'Low': return 'bg-blue-100 text-blue-800 hover:bg-blue-100';
        case 'Medium': return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-100';
        case 'High': return 'bg-orange-100 text-orange-800 hover:bg-orange-100';
        case 'Critical': return 'bg-red-100 text-red-800 hover:bg-red-100';
        default: return 'bg-gray-100 text-gray-800 hover:bg-gray-100';
    }
};

const PendingAcceptancesPage = () => {
  const [tasks, setTasks] = useState([]);
  const [loading, setLoading] = useState(true);
  const { toast } = useToast();

  const loadTasks = async () => {
    setLoading(true);
    const res = await getMyTasks('Pending', 'All');
    if (res.success) {
      setTasks(res.data);
    } else {
      toast({ title: "Error loading pending tasks", description: res.error, variant: "destructive" });
    }
    setLoading(false);
  };

  useEffect(() => {
    loadTasks();
  }, []);

  const handleAccept = async (assignmentId) => {
    const res = await acceptTaskAssignment(assignmentId);
    if (res.success) {
      toast({ title: "Task accepted!", description: "You can now update its progress in My Tasks." });
      loadTasks();
    } else {
      toast({ title: "Error accepting task", description: res.error, variant: "destructive" });
    }
  };

  const handleDecline = async (assignmentId) => {
    if(!window.confirm("Are you sure you want to decline this task?")) return;
    
    const res = await declineTaskAssignment(assignmentId);
    if (res.success) {
      toast({ title: "Task declined." });
      loadTasks();
    } else {
      toast({ title: "Error declining task", description: res.error, variant: "destructive" });
    }
  };

  return (
    <div className="max-w-7xl mx-auto space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-[#003D82]">Pending Acceptances</h1>
        <p className="text-gray-500">Review tasks assigned to you. Accept to start working or decline if unavailable.</p>
      </div>

      {loading ? (
        <div className="flex justify-center py-12">
          <Loader2 className="w-8 h-8 animate-spin text-[#003D82]" />
        </div>
      ) : tasks.length === 0 ? (
        <div className="text-center py-16 bg-white rounded-lg border border-dashed shadow-sm">
          <Check className="w-12 h-12 mx-auto text-green-400 mb-3" />
          <h3 className="text-lg font-medium text-gray-900">All Caught Up!</h3>
          <p className="text-gray-500">You have no pending task assignments.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {tasks.map(task => {
            const isOverdue = new Date(task.deadline) < new Date();

            return (
            <Card key={task.assignment_id} className="flex flex-col border-t-4 border-t-gray-300">
              <CardHeader className="pb-2">
                <div className="flex justify-between items-start mb-2">
                  <div className="flex gap-2 flex-wrap">
                      <Badge className={getPriorityColor(task.priority)}>{task.priority}</Badge>
                      {task.task_categories && (
                          <Badge variant="outline" style={{borderColor: task.task_categories.color, color: task.task_categories.color, backgroundColor: `${task.task_categories.color}15`}}>
                              {task.task_categories.name}
                          </Badge>
                      )}
                  </div>
                  <Badge variant="outline" className="bg-gray-100 text-gray-700 border-gray-200 flex items-center">
                    <Clock className="w-3 h-3 mr-1" /> Pending
                  </Badge>
                </div>
                <h3 className="font-bold text-lg text-gray-900">{task.title}</h3>
                {task.created_by_profile && (
                    <div className="flex items-center text-xs text-gray-500 mt-1">
                        <User className="w-3 h-3 mr-1" />
                        Assigned by {task.created_by_profile.full_name}
                    </div>
                )}
              </CardHeader>
              <CardContent className="flex-1 pb-4">
                <p className="text-sm text-gray-600 mb-4">{task.description}</p>
                
                <div className="mt-auto pt-4 border-t border-gray-100">
                  {task.deadline && (
                    <div className={`flex items-center text-sm p-2 rounded-md ${isOverdue ? 'bg-red-50 text-red-700' : 'bg-gray-50 text-gray-600'}`}>
                      {isOverdue ? <AlertCircle className="w-4 h-4 mr-2" /> : <Calendar className="w-4 h-4 mr-2" />}
                      <span className="font-medium">
                          Due: {format(new Date(task.deadline), 'MMM dd, yyyy')} {isOverdue && '(Overdue)'}
                      </span>
                    </div>
                  )}
                </div>
              </CardContent>
              <CardFooter className="bg-gray-50/50 border-t p-3 flex gap-3">
                <Button 
                    variant="outline" 
                    className="flex-1 border-red-200 text-red-600 hover:bg-red-50 hover:text-red-700"
                    onClick={() => handleDecline(task.assignment_id)}
                >
                    <X className="w-4 h-4 mr-2" /> Decline
                </Button>
                <Button 
                    className="flex-1 bg-[#003D82] hover:bg-[#002a5a] text-white"
                    onClick={() => handleAccept(task.assignment_id)}
                >
                    <Check className="w-4 h-4 mr-2" /> Accept
                </Button>
              </CardFooter>
            </Card>
          )})}
        </div>
      )}
    </div>
  );
};

export default PendingAcceptancesPage;