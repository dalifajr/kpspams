class BillModel {
  final int id;
  final String periodLabel;
  final int totalAmount;
  final int paidAmount;
  final int remaining;
  final String? usageM3;
  final String status; // published, partial, paid

  BillModel({
    required this.id,
    required this.periodLabel,
    required this.totalAmount,
    required this.paidAmount,
    required this.remaining,
    this.usageM3,
    required this.status,
  });

  factory BillModel.fromJson(Map<String, dynamic> json) {
    return BillModel(
      id: json['id'] ?? 0,
      periodLabel: json['period_label'] ?? '',
      totalAmount: json['total_amount'] ?? 0,
      paidAmount: json['paid_amount'] ?? 0,
      remaining: json['remaining'] ?? 0,
      usageM3: json['usage_m3']?.toString(),
      status: json['status'] ?? 'published',
    );
  }
}
