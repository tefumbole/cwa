// Service for handling Role-Based Access Control using localStorage

const ROLES_KEY = 'ab_roles_v2';
const DEFAULT_PERMISSIONS = [
  'DASHBOARD_VIEW',
  'TIMESHEETS_VIEW', 'TIMESHEETS_CREATE', 'TIMESHEETS_EDIT',
  'ACTIVITIES_CREATE', 'ACTIVITIES_EDIT', 'ACTIVITIES_DELETE',
  'FILL_TIMESHEET_CREATE', 'FILL_TIMESHEET_EDIT',
  'USERS_VIEW', 'USERS_CREATE', 'USERS_EDIT', 'USERS_DELETE',
  'ROLES_VIEW', 'ROLES_CREATE', 'ROLES_EDIT', 'ROLES_DELETE',
  'NOTIFICATIONS_VIEW', 'NOTIFICATIONS_CREATE', 'NOTIFICATIONS_EDIT', 'NOTIFICATIONS_DELETE',
  'SEND_LETTERS_VIEW', 'SEND_LETTERS_CREATE', 'SEND_LETTERS_EDIT', 'SEND_LETTERS_DELETE',
  'TEMPLATES_VIEW', 'TEMPLATES_CREATE', 'TEMPLATES_EDIT', 'TEMPLATES_DELETE',
  'HISTORY_VIEW', 'REPORTS_VIEW',
  'COURSES_VIEW', 'COURSES_CREATE', 'COURSES_EDIT', 'COURSES_DELETE',
  'REGISTRATION_VIEW', 'REGISTRATION_CREATE', 'REGISTRATION_EDIT', 'REGISTRATION_DELETE',
  'QUOTES_VIEW', 'QUOTES_CREATE',
  // New Permissions
  'MEMBERS_ADD', 'MEMBERS_EDIT', 'MEMBERS_DELETE', 'MEMBERS_VIEW',
  'SHAREHOLDERS_ADD', 'SHAREHOLDERS_EDIT', 'SHAREHOLDERS_DELETE', 'SHAREHOLDERS_VIEW',
  'SETTINGS_VIEW', 'SETTINGS_EDIT',
  'EVENTS_ADD', 'EVENTS_EDIT', 'EVENTS_DELETE', 'EVENTS_VIEW',
  'menu.dashboard', 'menu.events', 'menu.invitations', 'menu.event_templates',
  'menu.tasks', 'menu.jobs', 'menu.users', 'menu.members', 'menu.shareholders',
  'menu.courses', 'menu.announcements', 'menu.timesheets', 'menu.operations',
  'menu.system', 'menu.roles',
];

const DEFAULT_ROLES = [
  {
    id: 'role-super-admin',
    name: 'Super Admin',
    description: 'Full system access',
    is_default: true,
    permissions: DEFAULT_PERMISSIONS // All permissions
  },
  {
    id: 'role-manager',
    name: 'Manager',
    description: 'Operational management',
    is_default: true,
    permissions: [
      'DASHBOARD_VIEW',
      'TIMESHEETS_VIEW', 'TIMESHEETS_CREATE', 'TIMESHEETS_EDIT',
      'REPORTS_VIEW',
      'USERS_VIEW',
      'EVENTS_VIEW', 'EVENTS_CREATE', 'EVENTS_EDIT',
      'COURSES_VIEW', 'REGISTRATION_VIEW',
      'MEMBERS_VIEW', 'SHAREHOLDERS_VIEW',
      'EVENTS_VIEW'
    ]
  },
  {
    id: 'role-employee',
    name: 'Employee',
    description: 'Standard access',
    is_default: true,
    permissions: [
      'DASHBOARD_VIEW',
      'FILL_TIMESHEET_CREATE', 'FILL_TIMESHEET_EDIT'
    ]
  }
];

const initRoles = () => {
  if (!localStorage.getItem(ROLES_KEY)) {
    localStorage.setItem(ROLES_KEY, JSON.stringify(DEFAULT_ROLES));
  }
};

const getRoles = () => {
  initRoles();
  return JSON.parse(localStorage.getItem(ROLES_KEY) || '[]');
};

const saveRoles = (roles) => {
  localStorage.setItem(ROLES_KEY, JSON.stringify(roles));
};

export const getAllRolePermissions = async () => {
  return getRoles();
};

export const createRoleWithPermissions = async (roleName, description, permissions) => {
  const roles = getRoles();
  const newRole = {
    id: `role-${Date.now()}`,
    name: roleName,
    description: description || '',
    is_default: false,
    permissions: permissions || [],
    created_at: new Date().toISOString()
  };
  roles.push(newRole);
  saveRoles(roles);
  return newRole;
};

export const updateRolePermissions = async (roleId, permissions) => {
  const roles = getRoles();
  const index = roles.findIndex(r => r.id === roleId);
  if (index === -1) throw new Error("Role not found");
  
  if (roles[index].id === 'role-super-admin') {
      roles[index].permissions = DEFAULT_PERMISSIONS;
  } else {
      roles[index].permissions = permissions;
  }
  
  saveRoles(roles);
  return roles[index];
};

export const getUserPermissions = async (userId) => {
    const users = JSON.parse(localStorage.getItem('alpha_users') || '[]');
    const user = users.find(u => u.id === userId);
    
    if (!user) {
        if (userId === 'admin-master') return DEFAULT_PERMISSIONS;
        return []; 
    }

    const roles = getRoles();
    const role = roles.find(r => r.id === user.role_id);
    return role ? role.permissions : [];
};

export const hasPermission = async (userId, permissionKey) => {
    const permissions = await getUserPermissions(userId);
    return permissions.includes(permissionKey);
};

export const getPermissionsByRole = async (roleId) => {
    const roles = getRoles();
    const role = roles.find(r => r.id === roleId);
    return role ? role.permissions : [];
};

export const PERMISSION_KEYS = DEFAULT_PERMISSIONS;