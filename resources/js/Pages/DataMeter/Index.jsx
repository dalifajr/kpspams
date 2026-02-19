import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, Section, EmptyState } from '@/Layouts/AppLayout';
import Card from '@/Components/Card';
import Alert from '@/Components/Alert';
import Button from '@/Components/Button';

const MONTHS = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const MONTHS_SHORT = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

export default function DataMeterIndex({ periods = [], year = 2026, yearOptions = [] }) {
    const { flash, auth } = usePage().props;

    if (!auth?.user?.is_admin) {
        return (
            <AppLayout>
                <Head title="Data Meter" />
                <PageContainer>
                    <TopAppBar title="Data Meter" backHref="/dashboard" />
                    <div style={{ padding: '0 16px' }}>
                        <Alert type="error">Anda tidak memiliki akses ke halaman ini.</Alert>
                    </div>
                </PageContainer>
            </AppLayout>
        );
    }

    return (
        <AppLayout>
            <Head title="Data Meter" />
            <PageContainer>
                <TopAppBar
                    title="Data Meter"
                    backHref="/dashboard"
                    actions={
                        <select
                            value={year}
                            onChange={(e) => router.get('/menu/data-meter', { year: e.target.value })}
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
                    {flash?.status && <Alert type="success" className="mb-4">{flash.status}</Alert>}
                    {flash?.error && <Alert type="error" className="mb-4">{flash.error}</Alert>}

                    {/* Info Card */}
                    <Card variant="filled" className="mb-4" style={{ backgroundColor: 'var(--md-sys-color-secondary-container)', color: 'var(--md-sys-color-on-secondary-container)' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                            <span className="material-symbols-rounded" style={{ fontSize: '40px' }}>analytics</span>
                            <div>
                                <div className="md-title-medium" style={{ fontWeight: 600 }}>Menu Data Meter</div>
                                <div className="md-body-small">
                                    Kelola data meter, batalkan penerbitan tagihan, dan lihat riwayat pembayaran.
                                </div>
                            </div>
                        </div>
                    </Card>

                    {/* Periods List */}
                    <Section title={`Periode Tahun ${year}`}>
                        {periods.length > 0 ? (
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                                {periods.map((period) => (
                                    <Link
                                        key={period.id}
                                        href={`/menu/data-meter/${period.id}`}
                                        style={{ textDecoration: 'none' }}
                                    >
                                        <Card variant="outlined" style={{ cursor: 'pointer' }}>
                                            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                                                    <div style={{
                                                        width: '48px',
                                                        height: '48px',
                                                        borderRadius: 'var(--shape-expressive-small)',
                                                        background: 'var(--md-sys-color-primary-container)',
                                                        display: 'flex',
                                                        alignItems: 'center',
                                                        justifyContent: 'center',
                                                        color: 'var(--md-sys-color-on-primary-container)'
                                                    }}>
                                                        <span className="material-symbols-rounded">calendar_month</span>
                                                    </div>
                                                    <div>
                                                        <div className="md-title-medium" style={{ fontWeight: 600 }}>
                                                            {MONTHS[period.month]} {period.year}
                                                        </div>
                                                        <div className="md-body-small text-muted">
                                                            {period.summary?.recorded || 0}/{period.summary?.total || 0} Tercatat •
                                                            {period.summary?.published || 0} Terbit •
                                                            {period.summary?.paid || 0} Lunas
                                                        </div>
                                                    </div>
                                                </div>
                                                <div style={{ textAlign: 'right' }}>
                                                    <div className="md-title-small" style={{ color: 'var(--md-sys-color-primary)' }}>
                                                        Rp {(period.summary?.total_bill || 0).toLocaleString('id-ID')}
                                                    </div>
                                                    <span className="material-symbols-rounded" style={{ color: 'var(--md-sys-color-outline)' }}>
                                                        chevron_right
                                                    </span>
                                                </div>
                                            </div>
                                        </Card>
                                    </Link>
                                ))}
                            </div>
                        ) : (
                            <EmptyState
                                icon="event_busy"
                                title="Tidak Ada Periode"
                                message={`Belum ada periode pencatatan meter di tahun ${year}.`}
                            />
                        )}
                    </Section>
                </div>
            </PageContainer>
        </AppLayout>
    );
}
