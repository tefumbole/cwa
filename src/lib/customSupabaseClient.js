import { createClient } from '@supabase/supabase-js';

const supabaseUrl = 'https://xnfurysmtmxkfsdjghow.supabase.co';
const supabaseAnonKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InhuZnVyeXNtdG14a2ZzZGpnaG93Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzA1NTMyMDEsImV4cCI6MjA4NjEyOTIwMX0.VBmIxYtdIyL68g3YZ-InQtw9hztEFm11yFtC_2vVbRk';

const customSupabaseClient = createClient(supabaseUrl, supabaseAnonKey);

export default customSupabaseClient;

export { 
    customSupabaseClient,
    customSupabaseClient as supabase,
};
