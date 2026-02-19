import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, Section, EmptyState } from '@/Layouts/AppLayout';
import Input, { Select, Textarea } from '@/Components/Input';
import Card from '@/Components/Card';
import Alert from '@/Components/Alert';
import Button, { IconButton } from '@/Components/Button';
import { BottomSheet, ConfirmModal } from '@/Components/Modal';

const MONTHS = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const MONTHS_SHORT = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

export default function MeterReadingIndex({ periods = [], year = 2026, yearOptions = [], monthOptions = {}, nextPeriod }) {
    const { flash, auth } = usePage().props;
    const currentPeriod = periods[0];
    const [showOpenForm, setShowOpenForm] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [deleteTarget, setDeleteTarget] = useState(null);
    const openPeriodForm = useForm({
        year: nextPeriod?.year || year,
        month: nextPeriod?.month || 1,
        notes: '',
    });

    const handleOpenPeriod = (e) => {
        e.preventDefault();
        openPeriodForm.post('/catat-meter');
    };

    const confirmDeletePeriod = (period) => {
        setDeleteTarget(period);
        setShowDeleteModal(true);
    };

    const handleDeletePeriod = () => {
        if (!deleteTarget) return;
        router.delete(`/catat-meter/${deleteTarget.id}`, {
            onSuccess: () => {
                setShowDeleteModal(false);
                setDeleteTarget(null);
            },
        });
    };

    return (
        <AppLayout>
            <Head title="Catat Meter" />
            <PageContainer>
                <TopAppBar 
                    title="Catat Meter" 
                    backHref="/dashboard"
                    actions={
                        <select
                            value={year}
                            onChange={(e) => router.get('/catat-meter', { year: e.target.value })}
                            style={{ 
                                border: 'none', 
                                background: 'transparent', 
                                fontSize: '1rem', 
                                fontWeight: 600, 
                                color: 'var(--md-sys-color-primary)',
                                cursor: 'pointer'
                            }}
                        >
                            {yearOptions.map(y => <option key={y} value={y}>{y}</option>)}
                        </select>
                    }
                />

                <div style={{ padding: '0 16px' }}>
                    {flash?.success && (
                        <Alert type="success" className="mb-4">{flash.success}</Alert>
                    )}

                    {/* Admin open period */}
                    {auth?.user?.is_admin && (
                        <div className="mb-4">
                            <Button
                                variant="tonal"
                                fullWidth
                                icon="playlist_add"
                                onClick={() => setShowOpenForm(true)}
                            >
                                Buka Periode Pencatatan
                            </Button>
                        </div>
                    )}

                    {/* Current Period Hero Card */}
                    {currentPeriod && (
                        <div className="md-hero-card mb-4" style={{ position: 'relative' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
                                <div>
                                    <div className="md-label-medium" style={{ opacity: 0.8 }}>PERIODE SAAT INI</div>
                                    <div className="md-headline-medium">{MONTHS[currentPeriod.month]}</div>
                                </div>
                                <div style={{ 
                                    background: 'rgba(255,255,255,0.2)', 
                                    padding: '12px', 
                                    borderRadius: 'var(--shape-expressive-medium)' 
                                }}>
                                    <span className="material-symbols-rounded" style={{ fontSize: '32px' }}>edit_note</span>
                                </div>
                            </div>

                            {auth?.user?.is_admin && currentPeriod.can_delete && (
                                <div style={{ position: 'absolute', top: '16px', right: '16px' }}>
                                    <IconButton
                                        icon="delete"
                                        onClick={() => confirmDeletePeriod(currentPeriod)}
                                        style={{ color: 'var(--md-sys-color-on-primary)' }}
                                    />
                                </div>
                            )}

                            {/* Progress Bar */}
                            <div style={{ 
                                display: 'flex', 
                                gap: '4px', 
                                height: '8px', 
                                borderRadius: '4px', 
                                overflow: 'hidden', 
                                background: 'rgba(0,0,0,0.15)', 
                                marginBottom: '12px' 
                            }}>
                                <div style={{ 
                                    width: `${currentPeriod.summary?.progress || 0}%`, 
                                    background: '#fff',
                                    borderRadius: '4px',
                                    transition: 'width 0.3s ease'
                                }} />
                            </div>

                            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.875rem' }}>
                                <span>{currentPeriod.summary?.completed || 0} Selesai</span>
                                <span style={{ fontWeight: 600 }}>{currentPeriod.summary?.progress || 0}%</span>
                            </div>
                            <Link
                                href={`/catat-meter/${currentPeriod.id}`}
                                style={{ textDecoration: 'none', color: 'inherit', display: 'block', marginTop: '16px' }}
                            >
                                <div className="md-btn md-btn-text" style={{ justifyContent: 'flex-start' }}>
                                    <span className="material-symbols-rounded">chevron_right</span>
                                    Lihat detail periode
                                </div>
                            </Link>
                        </div>
                    )}

                    {/* History */}
                    <Section title="Riwayat Periode">
                        {periods.length > 1 ? (
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                                {periods.slice(1).map((period) => (
                                    <div
                                        key={period.id}
                                        className="md-card-outlined"
                                        style={{
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'space-between',
                                            textDecoration: 'none',
                                            color: 'inherit'
                                        }}
                                    >
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                                            <div style={{ 
                                                width: '44px', 
                                                height: '44px', 
                                                borderRadius: 'var(--shape-expressive-small)', 
                                                background: 'var(--md-sys-color-surface-container-high)', 
                                                display: 'flex', 
                                                alignItems: 'center', 
                                                justifyContent: 'center' 
                                            }}>
                                                <span className="material-symbols-rounded" style={{ color: 'var(--md-sys-color-on-surface-variant)' }}>
                                                    calendar_today
                                                </span>
                                            </div>
                                            <div>
                                                <div className="md-title-small">
                                                    {MONTHS_SHORT[period.month]} {period.year}
                                                </div>
                                                <div className="md-body-small text-muted">
                                                    {period.summary?.volume?.toFixed(0) || 0} m³ • Rp {(period.summary?.bill || 0).toLocaleString('id-ID')}
                                                </div>
                                            </div>
                                        </div>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                            {auth?.user?.is_admin && period.can_delete && (
                                                <IconButton
                                                    icon="delete"
                                                    onClick={() => confirmDeletePeriod(period)}
                                                />
                                            )}
                                            <Link
                                                href={`/catat-meter/${period.id}`}
                                                className="md-icon-btn"
                                                style={{ textDecoration: 'none' }}
                                            >
                                                <span className="material-symbols-rounded">chevron_right</span>
                                            </Link>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <EmptyState
                                icon="history"
                                title="Belum Ada Riwayat"
                                message="Riwayat periode akan muncul di sini"
                            />
                        )}
                    </Section>
                </div>
                <BottomSheet
                    isOpen={showOpenForm}
                    onClose={() => setShowOpenForm(false)}
                    title="Buka Periode Pencatatan"
                >
                    <form onSubmit={handleOpenPeriod} className="md-form-stack" style={{ padding: '16px 24px 24px' }}>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                            <Input
                                label="Tahun"
                                type="number"
                                value={openPeriodForm.data.year}
                                onChange={(e) => openPeriodForm.setData('year', e.target.value)}
                                min={2020}
                                max={2100}
                                required
                            />
                            <Select
                                label="Bulan"
                                value={openPeriodForm.data.month}
                                onChange={(e) => openPeriodForm.setData('month', e.target.value)}
                                required
                            >
                                {Object.entries(monthOptions).map(([value, label]) => (
                                    <option key={value} value={value}>{label}</option>
                                ))}
                            </Select>
                        </div>
                        <Textarea
                            label="Catatan (Opsional)"
                            rows={3}
                            value={openPeriodForm.data.notes}
                            onChange={(e) => openPeriodForm.setData('notes', e.target.value)}
                        />
                        <Button type="submit" variant="filled" fullWidth loading={openPeriodForm.processing} icon="check">
                            Simpan
                        </Button>
                    </form>
                </BottomSheet>

                <ConfirmModal
                    isOpen={showDeleteModal}
                    onClose={() => setShowDeleteModal(false)}
                    onConfirm={handleDeletePeriod}
                    title="Hapus Periode"
                    message={deleteTarget
                        ? `Hapus periode ${MONTHS_SHORT[deleteTarget.month]} ${deleteTarget.year}?`
                        : 'Hapus periode ini?'
                    }
                    confirmText="Hapus"
                    variant="danger"
                />
            </PageContainer>
        </AppLayout>
    );
}
