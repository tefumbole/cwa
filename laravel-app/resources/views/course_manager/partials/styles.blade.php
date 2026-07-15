<style>
    .cm-shell { max-width: 1200px; margin: 0 auto; }
    .cm-nav {
        display: flex; flex-wrap: wrap; gap: 4px 16px;
        border-bottom: 1px solid #e5e7eb; margin-bottom: 1.5rem;
    }
    .cm-nav a {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 12px 4px 14px; color: #64748b; text-decoration: none;
        font-weight: 600; font-size: 14px; border-bottom: 2px solid transparent;
        margin-bottom: -1px;
    }
    .cm-nav a:hover { color: #0b3f90; text-decoration: none; }
    .cm-nav a.is-active { color: #0b3f90; border-bottom-color: #0b3f90; }
    .cm-title { color: #0b3f90; font-weight: 800; font-size: 1.75rem; margin: 0 0 4px; }
    .cm-subtitle { color: #6b7280; margin: 0; }
    .cm-page-card {
        background: #fff; border: 1px solid #eef2f7; border-radius: 14px;
        box-shadow: 0 1px 3px rgba(15,23,42,.06); padding: 1.25rem; margin-bottom: 1rem;
    }
    .cm-btn-primary {
        background: #0b3f90; border: 1px solid #0b3f90; color: #fff;
        border-radius: 8px; padding: 8px 14px; font-weight: 600; font-size: 14px;
        display: inline-flex; align-items: center; gap: 6px; text-decoration: none; cursor: pointer;
    }
    .cm-btn-primary:hover { background: #0a3578; color: #fff; text-decoration: none; }
    .cm-btn-gold {
        background: #e8b923; border: 1px solid #e8b923; color: #111;
        border-radius: 8px; padding: 8px 14px; font-weight: 700; font-size: 14px;
        display: inline-flex; align-items: center; gap: 6px; text-decoration: none;
    }
    .cm-btn-gold:hover { background: #d4a820; color: #111; text-decoration: none; }
    .cm-btn-outline {
        border: 1px solid #d1d5db; background: #fff; color: #374151;
        border-radius: 8px; padding: 6px 12px; font-weight: 600; font-size: 13px;
        display: inline-flex; align-items: center; gap: 6px; text-decoration: none; cursor: pointer;
    }
    .cm-stat {
        background: #fff; border: 1px solid #eef2f7; border-radius: 12px;
        padding: 1rem 1.1rem; box-shadow: 0 1px 3px rgba(15,23,42,.05);
    }
    .cm-stat .label { font-size: 12px; color: #6b7280; font-weight: 600; margin: 0; }
    .cm-stat .value { font-size: 1.6rem; font-weight: 800; color: #111; margin: 4px 0 0; }
    .cm-stat .value.green { color: #16a34a; }
    .cm-badge {
        display: inline-block; padding: 3px 10px; border-radius: 999px;
        font-size: 12px; font-weight: 600; background: #f1f5f9; color: #334155;
    }
    .cm-price { color: #16a34a; font-weight: 800; }
    .cm-order form { display: inline; }
    .cm-order button {
        border: 0; background: #f1f5f9; color: #64748b; border-radius: 4px;
        width: 22px; height: 22px; line-height: 1; padding: 0; margin: 1px; cursor: pointer; font-size: 10px;
    }
</style>
