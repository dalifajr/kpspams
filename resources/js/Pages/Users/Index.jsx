import { Head, Link, usePage, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, EmptyState } from '@/Layouts/AppLayout';
import { SearchBar } from '@/Components/Input';
import Card from '@/Components/Card';
import { FAB, IconButton } from '@/Components/Button';
import Alert from '@/Components/Alert';
import { BottomSheet } from '@/Components/Modal';

export default function UsersIndex({ users = [], pendingUsers = [], tab = 'data', search = '', filters = {}, areas = [] }) {
    const { flash } = usePage().props;
    const [searchQuery, setSearchQuery] = useState(search || '');
    const [activeFilter, setActiveFilter] = useState(filters.role || 'all');
    const [showFilter, setShowFilter] = useState(false);
    const [sortOrder, setSortOrder] = useState(filters.sort === 'za' ? 'desc' : 'asc');
    const [areaFilter, setAreaFilter] = useState(filters.area || 'all');
    const activeTab = tab || 'data';
    const pendingCount = pendingUsers.length;

    const handleSearch = (value) => {
        router.get(
            '/menu/user',
            { q: value, tab: activeTab },
            { preserveState: true, replace: true }
        );
    };

    const baseUsers = activeTab === 'pending' ? pendingUsers : users;
    const roleFilter = activeTab === 'pending' ? 'all' : activeFilter;

    // Filter dan sort users
    let filteredUsers = baseUsers.filter(user => {
        if (roleFilter !== 'all' && user.role !== roleFilter) return false;
        if (areaFilter !== 'all' && user.area_id != areaFilter) return false;
        if (searchQuery) {
            const query = searchQuery.toLowerCase();
            return user.name?.toLowerCase().includes(query) || 
                   user.email?.toLowerCase().includes(query) ||
                   user.phone_number?.includes(query);
        }
        return true;
    });

    if (sortOrder === 'asc') {
        filteredUsers = [...filteredUsers].sort((a, b) => a.name?.localeCompare(b.name));
    } else {
        filteredUsers = [...filteredUsers].sort((a, b) => b.name?.localeCompare(a.name));
    }

    const getRoleBadgeClass = (role) => {
        switch(role) {
            case 'admin': return 'tertiary';
            case 'petugas': return 'secondary';
            default: return 'primary';
        }
    };

    return (
        <AppLayout>
            <Head title="Data User" />
            <PageContainer>
                <TopAppBar 
                    title="Data User" 
                    backHref="/dashboard"
                    actions={
                        <span className="md-badge primary">{users.length}</span>
                    }
                />

                <div style={{ padding: '0 16px' }}>
                    {flash?.success && (
                        <Alert type="success" className="mb-4">{flash.success}</Alert>
                    )}

                    {/* Search & Filter Bar */}
                    <div className="flex gap-2 mb-4">
                        <div className="flex-1">
                            <SearchBar
                                value={searchQuery}
                                onChange={setSearchQuery}
                                onSearch={handleSearch}
                                placeholder="Cari nama atau nomor..."
                            />
                        </div>
                        <IconButton 
                            icon="tune" 
                            onClick={() => setShowFilter(true)}
                            className="md-icon-btn tonal"
                        />
                    </div>

                    {/* Tabs */}
                    <div className="md-tab-bar mb-4">
                        <Link
                            href="/menu/user?tab=data"
                            className={`md-tab ${activeTab === 'data' ? 'active' : ''}`}
                        >
                            Data User
                        </Link>
                        <Link
                            href="/menu/user?tab=pending"
                            className={`md-tab ${activeTab === 'pending' ? 'active' : ''}`}
                        >
                            Menunggu Approval
                            {pendingCount > 0 && (
                                <span className="md-badge sm" style={{ marginLeft: '8px' }}>{pendingCount}</span>
                            )}
                        </Link>
                    </div>

                    {/* Role Filter Chips */}
                    {activeTab === 'data' && (
                        <div className="md-chip-group mb-4">
                            <button 
                                className={`md-chip ${activeFilter === 'all' ? 'selected' : ''}`}
                                onClick={() => setActiveFilter('all')}
                            >
                                <span className="material-symbols-rounded">group</span>
                                Semua
                            </button>
                            <button 
                                className={`md-chip ${activeFilter === 'admin' ? 'selected' : ''}`}
                                onClick={() => setActiveFilter('admin')}
                            >
                                Admin
                            </button>
                            <button 
                                className={`md-chip ${activeFilter === 'petugas' ? 'selected' : ''}`}
                                onClick={() => setActiveFilter('petugas')}
                            >
                                Petugas
                            </button>
                            <button 
                                className={`md-chip ${activeFilter === 'user' ? 'selected' : ''}`}
                                onClick={() => setActiveFilter('user')}
                            >
                                User
                            </button>
                        </div>
                    )}

                    {/* Users List */}
                    {filteredUsers.length === 0 ? (
                        <Card variant="outlined">
                            <EmptyState
                                icon="person_off"
                                title={activeTab === 'pending' ? 'Tidak Ada Approval' : 'Belum Ada User'}
                                message={
                                    activeTab === 'pending'
                                        ? 'Belum ada pengguna baru yang menunggu persetujuan.'
                                        : 'Belum ada user yang terdaftar. Gunakan tombol + untuk menambah pengguna baru.'
                                }
                            />
                        </Card>
                    ) : (
                        <Card variant="outlined" style={{ padding: 0, overflow: 'hidden' }}>
                            {filteredUsers.map((user) => (
                                <Link key={user.id} href={`/menu/user/${user.id}`} className="md-list-item">
                                    <div className="md-list-item__leading">
                                        {user.name?.charAt(0).toUpperCase()}
                                    </div>
                                    <div className="md-list-item__content">
                                        <div className="md-list-item__headline">{user.name}</div>
                                        <div className="md-list-item__supporting-text">
                                            <span className={`md-badge sm ${getRoleBadgeClass(user.role)}`}>{user.role}</span>
                                            <span>{user.phone_number || user.email}</span>
                                        </div>
                                    </div>
                                    <span className="material-symbols-rounded md-list-item__trailing">chevron_right</span>
                                </Link>
                            ))}
                        </Card>
                    )}

                    {/* Result Count */}
                    <div className="text-center mt-4 mb-6">
                        <span className="md-body-small text-muted">
                            {filteredUsers.length} dari {baseUsers.length} pengguna
                        </span>
                    </div>
                </div>

                <FAB icon="person_add" href="/menu/user/create" />

                {/* Filter Bottom Sheet */}
                <BottomSheet 
                    isOpen={showFilter} 
                    onClose={() => setShowFilter(false)}
                    title="Filter & Urutkan"
                >
                    <div className="md-form-stack">
                        {/* Sort Order */}
                        <div>
                            <label className="md-label-medium text-muted mb-2" style={{ display: 'block' }}>Urutkan</label>
                            <div className="md-chip-group">
                                <button 
                                    className={`md-chip ${sortOrder === 'asc' ? 'selected' : ''}`}
                                    onClick={() => setSortOrder('asc')}
                                >
                                    <span className="material-symbols-rounded">sort_by_alpha</span>
                                    A - Z
                                </button>
                                <button 
                                    className={`md-chip ${sortOrder === 'desc' ? 'selected' : ''}`}
                                    onClick={() => setSortOrder('desc')}
                                >
                                    <span className="material-symbols-rounded">sort_by_alpha</span>
                                    Z - A
                                </button>
                            </div>
                        </div>

                        {/* Area Filter */}
                        {areas && areas.length > 0 && (
                            <div>
                                <label className="md-label-medium text-muted mb-2" style={{ display: 'block' }}>Area</label>
                                <div className="md-chip-group" style={{ flexWrap: 'wrap' }}>
                                    <button 
                                        className={`md-chip ${areaFilter === 'all' ? 'selected' : ''}`}
                                        onClick={() => setAreaFilter('all')}
                                    >
                                        Semua Area
                                    </button>
                                    {areas.map(area => (
                                        <button 
                                            key={area.id}
                                            className={`md-chip ${areaFilter == area.id ? 'selected' : ''}`}
                                            onClick={() => setAreaFilter(area.id)}
                                        >
                                            {area.name}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        <button 
                            className="md-btn md-btn-tonal w-full mt-4"
                            onClick={() => {
                                setActiveFilter('all');
                                setAreaFilter('all');
                                setSortOrder('asc');
                                setShowFilter(false);
                            }}
                        >
                            <span className="material-symbols-rounded">restart_alt</span>
                            Reset Filter
                        </button>
                    </div>
                </BottomSheet>
            </PageContainer>
        </AppLayout>
    );
}
