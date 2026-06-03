import React, { createContext, useContext, useState, useEffect } from 'react';
import { useAuth } from '@/context/AuthContext';
import { getUserPermissions } from '@/services/rolePermissionService';

const PermissionContext = createContext({});

export const usePermission = () => useContext(PermissionContext);

export const PermissionProvider = ({ children }) => {
  const { user, role, loading: authLoading } = useAuth();
  const [permissions, setPermissions] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let mounted = true;

    const loadPermissions = async () => {
      if (authLoading) return; // Wait for auth to settle

      if (user) {
        try {
          // If admin, we don't necessarily need to fetch permissions from DB 
          // because we grant full access in hasPermission anyway.
          // But fetching them doesn't hurt for UI consistency if needed later.
          const perms = await getUserPermissions(user.id);
          if (mounted) setPermissions(perms || []);
        } catch (error) {
          console.error('PermissionContext Error:', error);
          if (mounted) setPermissions([]);
        }
      } else {
        if (mounted) setPermissions([]);
      }
      if (mounted) setLoading(false);
    };

    loadPermissions();

    return () => { mounted = false; };
  }, [user, authLoading]);

  const hasPermission = (permissionKey) => {
    if (!user) return false;
    
    // Admin Override - Grant all permissions
    if (role === 'admin' || role === 'super_admin' || role === 'director') {
        return true;
    }

    return Array.isArray(permissions) && permissions.includes(permissionKey);
  };

  return (
    <PermissionContext.Provider value={{
      permissions,
      loading: loading || authLoading,
      hasPermission
    }}>
      {children}
    </PermissionContext.Provider>
  );
};