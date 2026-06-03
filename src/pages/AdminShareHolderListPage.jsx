import React, { useState, useEffect } from 'react';
import { getAllShareholders, deleteShareholder } from '@/services/shareholderService';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { useToast } from '@/components/ui/use-toast';
import { Loader2, Search, Trash2, RefreshCw } from 'lucide-react';
import { format } from 'date-fns';

const AdminShareHolderListPage = () => {
  const [shareholders, setShareholders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const { toast } = useToast();

  const fetchShareholders = async () => {
    setLoading(true);
    try {
      const data = await getAllShareholders();
      setShareholders(data);
    } catch (error) {
      toast({
        title: "Error fetching data",
        description: error.message,
        variant: "destructive"
      });
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchShareholders();
  }, []);

  const handleDelete = async (id) => {
    if (!window.confirm("Are you sure you want to delete this shareholder record? This cannot be undone.")) return;

    try {
      await deleteShareholder(id);
      toast({
        title: "Success",
        description: "Record deleted successfully.",
        className: "bg-green-600 text-white"
      });
      fetchShareholders();
    } catch (error) {
      toast({
        title: "Error",
        description: "Failed to delete record.",
        variant: "destructive"
      });
    }
  };

  const filteredShareholders = shareholders.filter(s => 
    s.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    s.email?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    s.reference_number?.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="p-6 space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-3xl font-bold text-gray-900">Shareholder Management</h1>
        <Button onClick={fetchShareholders} variant="outline" className="gap-2">
          <RefreshCw className="h-4 w-4" /> Refresh
        </Button>
      </div>

      <Card>
        <CardHeader>
          <div className="flex flex-col md:flex-row justify-between gap-4">
            <CardTitle>Registered Shareholders</CardTitle>
            <div className="relative w-full md:w-72">
              <Search className="absolute left-2 top-2.5 h-4 w-4 text-gray-500" />
              <Input
                placeholder="Search name, email, ref..."
                className="pl-8"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
            </div>
          </div>
        </CardHeader>
        <CardContent>
          {loading ? (
            <div className="flex justify-center py-8">
              <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
            </div>
          ) : filteredShareholders.length === 0 ? (
            <div className="text-center py-8 text-gray-500">No shareholders found.</div>
          ) : (
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Reference</TableHead>
                    <TableHead>Name</TableHead>
                    <TableHead>Contact</TableHead>
                    <TableHead>Shares</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Date</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredShareholders.map((s) => (
                    <TableRow key={s.id}>
                      <TableCell className="font-mono text-xs">{s.reference_number}</TableCell>
                      <TableCell className="font-medium">{s.name}</TableCell>
                      <TableCell>
                        <div className="flex flex-col text-sm">
                          <span>{s.email}</span>
                          <span className="text-gray-500 text-xs">{s.phone}</span>
                        </div>
                      </TableCell>
                      <TableCell>{s.shares_assigned}</TableCell>
                      <TableCell>${s.total_amount?.toLocaleString()}</TableCell>
                      <TableCell>
                        <Badge variant={s.payment_status === 'paid' ? 'default' : 'secondary'} className={s.payment_status === 'paid' ? 'bg-green-600' : 'bg-yellow-500'}>
                          {s.payment_status || 'Pending'}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-sm text-gray-500">
                        {format(new Date(s.created_at), 'MMM dd, yyyy')}
                      </TableCell>
                      <TableCell className="text-right">
                        <Button
                          variant="ghost"
                          size="icon"
                          onClick={() => handleDelete(s.id)}
                          className="text-red-500 hover:text-red-700 hover:bg-red-50"
                        >
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
};

export default AdminShareHolderListPage;