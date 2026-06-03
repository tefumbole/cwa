import { supabase } from '@/lib/customSupabaseClient';

/**
 * DEPRECATED: Most role service functions removed
 * System now uses WhatsApp-based admin verification (whatsappAdminService.js)
 * Only formatRoleLabel() kept for backward compatibility
 */

// REMOVED FUNCTIONS (replaced by whatsappAdminService):
// - getCurrentUserRole()
// - isSuperAdmin()
// - isAdmin()
// - hasPermission()
// - getUserRoleIds()
// - getUserPermissions()
// - getAllRoles()
// - createRole()
// - updateRole()
// - deleteRole()
// - getAllUsersWithRoles()
// - assignRoleToUser()
// - getRolePermissions()
// - addPermissionToRole()
// - removePermissionFromRole()
// - createUserWithRole()

/**
 * Format role label for display (kept for backward compatibility)
 * @param {string|object|null|undefined} roleName - Role name to format
 * @returns {string} Formatted role label
 */
export const formatRoleLabel = (roleName) => {
  // Handle null/undefined
  if (!roleName) return 'No Role';
  
  // Handle object (extract name property if exists)
  if (typeof roleName === 'object') {
    if (roleName.name) {
      roleName = roleName.name;
    } else {
      return 'No Role';
    }
  }
  
  // Ensure it's a string
  if (typeof roleName !== 'string') return 'No Role';
  
  // Format the string
  return roleName
    .split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

// Legacy compatibility exports (these now just return default values)
export const getCurrentUserRole = async () => null;
export const isSuperAdmin = async () => false;
export const isAdmin = async () => false;
export const hasPermission = async () => false;
export const getUserRoleIds = async () => [];
export const getUserPermissions = async () => ({ success: false, data: [], error: 'Role system deprecated' });
export const getAllRoles = async () => ({ success: true, data: [] });
export const createRole = async () => ({ success: false, error: 'Role system deprecated' });
export const updateRole = async () => ({ success: false, error: 'Role system deprecated' });
export const deleteRole = async () => ({ success: false, error: 'Role system deprecated' });
export const getAllUsersWithRoles = async () => ({ success: true, data: [] });
export const assignRoleToUser = async () => ({ success: false, error: 'Role system deprecated' });
export const getRolePermissions = async () => ({ success: true, data: [] });
export const addPermissionToRole = async () => ({ success: false, error: 'Role system deprecated' });
export const removePermissionFromRole = async () => ({ success: false, error: 'Role system deprecated' });
export const createUserWithRole = async () => ({ success: false, error: 'Role system deprecated' });