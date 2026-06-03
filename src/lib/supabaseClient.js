import { createClient } from '@supabase/supabase-js';

const supabaseUrl = import.meta.env.VITE_SUPABASE_URL;
const supabaseAnonKey = import.meta.env.VITE_SUPABASE_ANON_KEY;

// Safety check (prevents silent white screen)
if (!supabaseUrl) {
  console.error("CRITICAL: VITE_SUPABASE_URL is not defined.");
}
if (!supabaseAnonKey) {
  console.error("CRITICAL: VITE_SUPABASE_ANON_KEY is not defined.");
}

export const supabase = createClient(
  supabaseUrl || '',
  supabaseAnonKey || '',
  {
    auth: {
      persistSession: true,
      autoRefreshToken: true,
      detectSessionInUrl: true,

      // 🔥 THIS FIXES YOUR PROBLEM
      storage: typeof window !== "undefined" ? window.localStorage : undefined,
      storageKey: "alpha_supabase_auth"
    }
  }
);

// Optional: expose for debugging
if (typeof window !== "undefined") {
  window.supabase = supabase;
}

export default supabase;