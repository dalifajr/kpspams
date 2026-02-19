import { Link, usePage, router } from '@inertiajs/react';

/**
 * Material You 3 Bottom Navigation Bar
 */
export default function BottomNav() {
    const { auth } = usePage().props;
    const user = auth?.user;

    if (!user) return null;

    // Build navigation items based on user role
    const navItems = [
        { href: '/dashboard', icon: 'home', label: 'Beranda' },
    ];

    // Add role-specific items
    if (user.is_admin || user.is_petugas) {
        navItems.push({ href: '/catat-meter', icon: 'edit_note', label: 'Catat' });
    }

    navItems.push({ href: '/profile', icon: 'person', label: 'Profil' });

    // Get current path for active state
    const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';

    const isActive = (href) => {
        if (href === '/dashboard') {
            return currentPath === '/dashboard';
        }
        return currentPath.startsWith(href);
    };

    const handleLogout = (e) => {
        e.preventDefault();
        router.post('/logout');
    };

    return (
        <nav className="md-navigation-bar">
            {navItems.map((item) => (
                <Link
                    key={item.href}
                    href={item.href}
                    className={`md-navigation-bar__item ${isActive(item.href) ? 'active' : ''}`}
                >
                    <div className="md-navigation-bar__icon">
                        <span className="material-symbols-rounded">{item.icon}</span>
                    </div>
                    <span>{item.label}</span>
                </Link>
            ))}

            <button 
                type="button" 
                onClick={handleLogout} 
                className="md-navigation-bar__item"
            >
                <div className="md-navigation-bar__icon">
                    <span className="material-symbols-rounded">logout</span>
                </div>
                <span>Keluar</span>
            </button>
        </nav>
    );
}

/**
 * Navigation Rail - For larger screens (tablet/desktop)
 * Optional component for future use
 */
export function NavigationRail({ items, activeHref }) {
    return (
        <aside style={{
            position: 'fixed',
            left: 0,
            top: 0,
            bottom: 0,
            width: '80px',
            background: 'var(--md-sys-color-surface)',
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            padding: '12px 0',
            borderRight: '1px solid var(--md-sys-color-outline-variant)',
            zIndex: 40
        }}>
            {items.map((item) => (
                <Link
                    key={item.href}
                    href={item.href}
                    style={{
                        display: 'flex',
                        flexDirection: 'column',
                        alignItems: 'center',
                        gap: '4px',
                        padding: '12px 16px',
                        textDecoration: 'none',
                        color: activeHref === item.href 
                            ? 'var(--md-sys-color-on-surface)' 
                            : 'var(--md-sys-color-on-surface-variant)',
                        fontSize: '0.75rem',
                        fontWeight: 500
                    }}
                >
                    <div style={{
                        width: '56px',
                        height: '32px',
                        borderRadius: '16px',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        background: activeHref === item.href 
                            ? 'var(--md-sys-color-secondary-container)' 
                            : 'transparent',
                        color: activeHref === item.href
                            ? 'var(--md-sys-color-on-secondary-container)'
                            : 'inherit'
                    }}>
                        <span className="material-symbols-rounded">{item.icon}</span>
                    </div>
                    <span>{item.label}</span>
                </Link>
            ))}
        </aside>
    );
}
