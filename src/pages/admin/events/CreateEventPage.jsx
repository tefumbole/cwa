import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { createEvent } from '@/services/eventService';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/use-toast';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Loader2, ArrowLeft, Image as ImageIcon } from 'lucide-react';

const CreateEventPage = () => {
    const [formData, setFormData] = useState({
        name: '',
        description: '',
        date: '',
        time: '',
        location: '',
        banner_url: ''
    });
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();
    const { toast } = useToast();

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        
        // Mock image upload logic if needed, just saving URL string for now
        const res = await createEvent(formData);
        if (res.success) {
            toast({ title: "Event created successfully!" });
            navigate('/admin/events');
        } else {
            toast({ title: "Failed to create event", description: res.error, variant: "destructive" });
        }
        setLoading(false);
    };

    return (
        <div className="max-w-3xl mx-auto space-y-6">
            <div className="flex items-center gap-4">
                <Button variant="ghost" onClick={() => navigate(-1)} className="p-2">
                    <ArrowLeft className="w-5 h-5" />
                </Button>
                <div>
                    <h1 className="text-3xl font-bold text-[#003D82]">Create New Event</h1>
                    <p className="text-gray-500">Set up the details for your upcoming digital event.</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Event Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleSubmit} className="space-y-5">
                        <div className="space-y-2">
                            <Label htmlFor="name">Event Name *</Label>
                            <Input id="name" name="name" required value={formData.name} onChange={handleChange} placeholder="e.g. Annual Gala Dinner" />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="description">Description</Label>
                            <Textarea id="description" name="description" value={formData.description} onChange={handleChange} rows={4} placeholder="Detailed information about the event..." />
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div className="space-y-2">
                                <Label htmlFor="date">Event Date *</Label>
                                <Input id="date" name="date" type="date" required value={formData.date} onChange={handleChange} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="time">Event Time *</Label>
                                <Input id="time" name="time" type="time" required value={formData.time} onChange={handleChange} />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="location">Location</Label>
                            <Input id="location" name="location" value={formData.location} onChange={handleChange} placeholder="Venue name or address" />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="banner_url">Banner Image URL</Label>
                            <div className="flex gap-2">
                                <div className="relative flex-1">
                                    <ImageIcon className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                                    <Input id="banner_url" name="banner_url" value={formData.banner_url} onChange={handleChange} placeholder="https://example.com/image.jpg" className="pl-9" />
                                </div>
                            </div>
                            {formData.banner_url && (
                                <div className="mt-3 h-32 rounded-lg overflow-hidden border">
                                    <img src={formData.banner_url} alt="Banner Preview" className="w-full h-full object-cover" onError={(e) => e.target.style.display='none'} />
                                </div>
                            )}
                        </div>

                        <div className="pt-4 border-t flex justify-end gap-3">
                            <Button type="button" variant="outline" onClick={() => navigate(-1)}>Cancel</Button>
                            <Button type="submit" disabled={loading} className="bg-[#003D82] text-white">
                                {loading ? <Loader2 className="w-4 h-4 mr-2 animate-spin" /> : null}
                                Save Event
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
};

export default CreateEventPage;