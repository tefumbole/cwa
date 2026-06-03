export const validateConfig = () => {
  const requiredVars = [
    'VITE_SUPABASE_URL',
    'VITE_SUPABASE_ANON_KEY'
  ];

  const missing = requiredVars.filter(
    (key) => !import.meta.env[key]
  );

  if (missing.length > 0) {
    console.error(
      `[Config] Missing environment variables: ${missing.join(', ')}`
    );
    return false;
  }
  
  return true;
};