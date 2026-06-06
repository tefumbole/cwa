import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import AdminHorizontalNav from '@/components/admin/AdminHorizontalNav';
import { EVENT_NAV } from '@/config/eventNavConfig';
import { createMeal } from '@/services/mealsService';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useToast } from '@/components/ui/use-toast';
import { Loader2 } from 'lucide-react';

const CreateMealPage = () => {
  const [form, setForm] = useState({ name: '', description: '', category: 'General' });
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const { toast } = useToast();

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!form.name.trim()) return;
    setLoading(true);
    try {
      await createMeal(form);
      toast({ title: 'Meal created' });
      navigate('/admin/events/meals');
    } catch (err) {
      toast({ title: 'Error', description: err.message, variant: 'destructive' });
    }
    setLoading(false);
  };

  return (
    <div className="max-w-2xl">
      <AdminHorizontalNav items={EVENT_NAV} title="Create Meal" />
      <Card>
        <CardHeader><CardTitle>Meal Details</CardTitle></CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div><Label>Name *</Label><Input required value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} /></div>
            <div><Label>Category</Label><Input value={form.category} onChange={(e) => setForm({ ...form, category: e.target.value })} /></div>
            <div><Label>Description</Label><Textarea rows={3} value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} /></div>
            <Button type="submit" disabled={loading} className="bg-[#003D82]">
              {loading ? <Loader2 className="w-4 h-4 animate-spin mr-2" /> : null} Save Meal
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  );
};

export default CreateMealPage;
