import React, { useState, useEffect } from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { supabase } from '@/lib/customSupabaseClient';
import { Loader2 } from 'lucide-react';
import AccessDeniedPage from '@/components/AccessDeniedPage';
import { isCurrentUserAdmin } from '@/services/whatsappAdminService';

const ProtectedRoute = ({ 
  children, 
  requireAdmin = false, 
  requireSuperAdmin = false, 
  requiredPermission = null 
}) => {
  const [loading, setLoading] = useState(true);
  const [authorized, setAuthorized] = useState(false);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [error, setError] = useState(null);
  const location = useLocation();

  useEffect(() => {
    let isMounted = true;
    let timeoutId;

    const verifyAccess = async () => {
      try {
        // Safety timeout to prevent infinite loading screens
        timeoutId = setTimeout(() => {
          if (isMounted && loading) {
            console.warn("ProtectedRoute: Access verification timed out.");
            setError("Verification timed out. Please refresh.");
            setLoading(false);
          }
        }, 10000); // 10 seconds timeout

        const { data, error: sessionError } = await supabase.auth.getSession();
        
        if (sessionError) {
          throw sessionError;
        }

        const session = data?.session;
        
        if (!session) {
          if (isMounted) {
            setIsAuthenticated(false);
            setLoading(false);
          }
          return;
        }

        if (isMounted) setIsAuthenticated(true);

        // Check admin status using WhatsApp-based verification
        if (requireAdmin || requireSuperAdmin || requiredPermission) {
          const adminStatus = await isCurrentUserAdmin();
          
          if (isMounted) {
            setAuthorized(adminStatus);
            setLoading(false);
          }
          return;
        }

        // If no specific requirement, just require auth
        if (isMounted) {
          setAuthorized(true);
          setLoading(false);
        }
      } catch (e) {
        console.error("ProtectedRoute: Access verification error:", e);
        if (isMounted) {
          setAuthorized(false);
          setIsAuthenticated(false);
          setError("Authentication failed. Please log in again.");
          setLoading(false);
        }
      } finally {
        if (timeoutId) clearTimeout(timeoutId);
      }
    };

    verifyAccess();

    return () => {
      isMounted = false;
      if (timeoutId) clearTimeout(timeoutId);
    };
  }, [requireAdmin, requireSuperAdmin, requiredPermission, location.pathname]);

  if (loading) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[60vh] bg-transparent">
        <Loader2 className="w-10 h-10 animate-spin text-[#003D82] mb-4" />
        <p className="text-gray-500 text-sm animate-pulse">Verifying access...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[60vh] text-center p-4">
        <p className="text-red-500 font-medium mb-4">{error}</p>
        <button 
          onClick={() => window.location.reload()} 
          className="px-4 py-2 bg-[#003D82] text-white rounded-md hover:bg-blue-800 transition-colors"
        >
          Retry
        </button>
      </div>
    );
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  if (!authorized) {
    return <AccessDeniedPage />;
  }

  return children;
};

export default ProtectedRoute;