/**
 * Permission keys for admin sidebar menu items.
 * Used with PermissionContext.hasPermission() — admins bypass all checks.
 */
export const MENU_PERMISSIONS = {
  dashboard: 'menu.dashboard',
  events: 'menu.events',
  invitations: 'menu.invitations',
  eventTemplates: 'menu.event_templates',
  tasks: 'menu.tasks',
  jobs: 'menu.jobs',
  users: 'menu.users',
  members: 'menu.members',
  shareholders: 'menu.shareholders',
  courses: 'menu.courses',
  announcements: 'menu.announcements',
  timesheets: 'menu.timesheets',
  operations: 'menu.operations',
  system: 'menu.system',
  roles: 'menu.roles',
};

export function itemVisible(hasPermission, permission) {
  if (!permission) return true;
  return hasPermission(permission);
}
