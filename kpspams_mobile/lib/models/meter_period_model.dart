class MeterPeriodSummaryModel {
  final int total;
  final int recorded;
  final int pending;
  final int published;
  final int paid;

  MeterPeriodSummaryModel({
    required this.total,
    required this.recorded,
    required this.pending,
    required this.published,
    required this.paid,
  });

  factory MeterPeriodSummaryModel.fromJson(Map<String, dynamic> json) {
    return MeterPeriodSummaryModel(
      total: json['total'] ?? 0,
      recorded: json['recorded'] ?? 0,
      pending: json['pending'] ?? 0,
      published: json['published'] ?? 0,
      paid: json['paid'] ?? 0,
    );
  }
}

class MeterPeriodModel {
  final int id;
  final int year;
  final int month;
  final String status;
  final String label;
  final String? openedAt;
  final MeterPeriodSummaryModel summary;

  MeterPeriodModel({
    required this.id,
    required this.year,
    required this.month,
    required this.status,
    required this.label,
    required this.summary,
    this.openedAt,
  });

  factory MeterPeriodModel.fromJson(Map<String, dynamic> json) {
    return MeterPeriodModel(
      id: json['id'] ?? 0,
      year: json['year'] ?? 0,
      month: json['month'] ?? 0,
      status: json['status'] ?? 'open',
      label: json['label'] ?? '',
      openedAt: json['opened_at'],
      summary: MeterPeriodSummaryModel.fromJson(json['summary'] ?? {}),
    );
  }
}
