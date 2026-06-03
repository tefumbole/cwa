import { supabase } from '@/lib/customSupabaseClient';

export const fetchShareholders = async () => {
  try {
    // Attempt to fetch from profiles with role shareholder, or a shareholders table if it exists.
    // Based on standard schema, we'll look at profiles where role='shareholder'
    const { data, error } = await supabase
      .from('profiles')
      .select('id, full_name, email, phone')
      .eq('role', 'shareholder')
      .order('full_name');

    if (error) {
      console.error('Error fetching shareholders from profiles:', error);
      // Fallback: maybe there is a shareholders table
      const { data: shData, error: shError } = await supabase
        .from('shareholders')
        .select('id, name, email, phone')
        .order('name');
        
      if (!shError && shData) {
        return shData.map(s => ({
          ...s,
          full_name: s.name,
          type: 'shareholder'
        }));
      }
      return [];
    }

    return (data || []).map(p => ({
      ...p,
      name: p.full_name,
      type: 'shareholder'
    }));
  } catch (error) {
    console.error('Exception fetching shareholders:', error);
    return [];
  }
};

export const fetchStudents = async () => {
  try {
    const { data, error } = await supabase
      .from('students')
      .select('id, name, email, phone')
      .order('name');

    if (error) throw error;
    
    return (data || []).map(s => ({
      ...s,
      type: 'student'
    }));
  } catch (error) {
    console.error('Error fetching students:', error);
    return [];
  }
};

export const fetchStaff = async () => {
  try {
    const { data, error } = await supabase
      .from('members')
      .select('id, name, email, phone')
      .order('name');

    if (error) throw error;

    return (data || []).map(m => ({
      ...m,
      type: 'staff'
    }));
  } catch (error) {
    console.error('Error fetching staff:', error);
    return [];
  }
};

export const fetchAllRecipients = async () => {
  const [shareholders, students, staff] = await Promise.all([
    fetchShareholders(),
    fetchStudents(),
    fetchStaff()
  ]);

  return {
    shareholders,
    students,
    staff
  };
};