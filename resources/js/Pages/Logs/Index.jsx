import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, EmptyState } from '@/Layouts/AppLayout';
import Input, { Select } from '@/Components/Input';
import Card from '@/Components/Card';
import Alert from '@/Components/Alert';
import Button, { FAB } from '@/Components/Button';
import { BottomSheet } from '@/Components/Modal';

const formatDate = (value) => {
    if (!value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleString('id-ID');
};

export default function LogsIndex({ logs = [], filters = {}, undoableIds = [], missingTable = false, databaseName = null }) {
    const { flash } = usePage().props;
    const [showFilters, setShowFilters] = useState(false);

    const handleFilter = (key, value) => {
        router.get('/menu/logs-perubahan', { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    const handleUndo = (logId) => {
        router.post(`/menu/logs-perubahan/${logId}/undo`, {}, { preserveScroll: true });
    };

    return (
        <AppLayout>
            <Head title="Logs Perubahan" />
            <PageContainer>
                <TopAppBar title="Logs Perubahan" backHref="/dashboard" />

                <div style={{ padding: '0 16px' }}>
                    {missingTable && (
                        <Alert type="error" className="mb-4">
                            Tabel log belum tersedia di database. Pastikan web server memakai database yang sama dengan saat menjalankan migrasi.
                            {databaseName ? (
                                <div style={{ marginTop: '8px' }}>
                                    Database aktif: <strong>{databaseName}</strong>
                                </div>
                            ) : null}
                        </Alert>
                    )}
                    {flash?.status && (
                        <Alert type="success" className="mb-4">{flash.status}</Alert>
                    )}
                    {flash?.error && (
                        <Alert type="error" className="mb-4">{flash.error}</Alert>
                    )}

                    <FAB
                        icon="filter_list"
                        variant="secondary"
                        className="logs-filter-fab"
                        onClick={() => setShowFilters(true)}
                    />

                    {logs.length === 0 ? (
                        <Card variant="outlined">
                            <EmptyState
                                icon="history"
                                title="Belum Ada Perubahan"
                                message="Log perubahan akan muncul di sini." 
                            />
                        </Card>
                    ) : (
                        <Card variant="outlined" style={{ padding: 0, overflow: 'hidden' }}>
                            {logs.map((log) => {
                                const canUndo = undoableIds.includes(log.id) && log.undoable;
                                return (
                                    <div key={log.id} className="md-list-item" style={{ alignItems: 'flex-start' }}>
                                        <div className="md-list-item__content">
                                            <div className="md-list-item__headline">
                                                {log.description}
                                            </div>
                                            <div className="md-body-small text-muted" style={{ marginTop: '4px' }}>
                                                {formatDate(log.created_at)}
                                            </div>
                                            <div className="md-body-small" style={{ marginTop: '6px' }}>
                                                <span className="md-badge sm">{log.role || 'unknown'}</span>
                                                <span style={{ marginLeft: '8px' }}>
                                                    {log.user?.name || 'Sistem'}
                                                </span>
                                                {log.undone_at && (
                                                    <span className="md-badge sm" style={{ marginLeft: '8px' }}>Undo</span>
                                                )}
                                            </div>
                                        </div>
                                        <div>
                                            {canUndo ? (
                                                <Button
                                                    variant="text"
                                                    icon="undo"
                                                    onClick={() => handleUndo(log.id)}
                                                >
                                                    Undo
                                                </Button>
                                            ) : (
                                                <Button variant="text" icon="undo" disabled>
                                                    Undo
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                );
                            })}
                        </Card>
                    )}
                </div>

                <BottomSheet
                    isOpen={showFilters}
                    onClose={() => setShowFilters(false)}
                    title="Filter Logs"
                >
                    <div className="md-form-stack" style={{ padding: '16px 24px 24px' }}>
                        <Input
                            label="Dari Tanggal"
                            type="date"
                            value={filters.from || ''}
                            onChange={(e) => handleFilter('from', e.target.value)}
                        />
                        <Input
                            label="Sampai Tanggal"
                            type="date"
                            value={filters.to || ''}
                            onChange={(e) => handleFilter('to', e.target.value)}
                        />
                        <Select
                            label="Role"
                            value={filters.role || 'all'}
                            onChange={(e) => handleFilter('role', e.target.value)}
                        >
                            <option value="all">Semua</option>
                            <option value="admin">Admin</option>
                            <option value="petugas">Petugas</option>
                            <option value="user">User</option>
                        </Select>
                        <Button variant="tonal" fullWidth icon="close" onClick={() => setShowFilters(false)}>
                            Tutup
                        </Button>
                    </div>
                </BottomSheet>
            </PageContainer>
        </AppLayout>
    );
}
