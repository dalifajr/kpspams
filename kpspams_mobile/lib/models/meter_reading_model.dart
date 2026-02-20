class CustomerModel {
  final int id;
  final String customerCode;
  final String name;

  CustomerModel({
    required this.id,
    required this.customerCode,
    required this.name,
  });

  factory CustomerModel.fromJson(Map<String, dynamic> json) {
    return CustomerModel(
      id: json['id'] ?? 0,
      customerCode: json['customer_code'] ?? '',
      name: json['name'] ?? '',
    );
  }
}

class MeterReadingModel {
  final int id;
  final int meterPeriodId;
  final int customerId;
  final String? startReading;
  final String? endReading;
  final String? usageM3;
  final String? status;
  final String? recordedAt;
  final String? billPublishedAt;
  final int? billId;
  final String? billStatus;
  final int? billRemaining;
  final CustomerModel? customer;

  MeterReadingModel({
    required this.id,
    required this.meterPeriodId,
    required this.customerId,
    this.startReading,
    this.endReading,
    this.usageM3,
    this.status,
    this.recordedAt,
    this.billPublishedAt,
    this.billId,
    this.billStatus,
    this.billRemaining,
    this.customer,
  });

  factory MeterReadingModel.fromJson(Map<String, dynamic> json) {
    return MeterReadingModel(
      id: json['id'] ?? 0,
      meterPeriodId: json['meter_period_id'] ?? 0,
      customerId: json['customer_id'] ?? 0,
      startReading: json['start_reading']?.toString(),
      endReading: json['end_reading']?.toString(),
      usageM3: json['usage_m3']?.toString(),
      status: json['status'],
      recordedAt: json['recorded_at'],
        billPublishedAt: json['bill_published_at'],
        billId: json['bill']?['id'],
        billStatus: json['bill']?['status'],
        billRemaining: json['bill']?['remaining'],
      customer: json['customer'] != null
          ? CustomerModel.fromJson(json['customer'])
          : null,
    );
  }
}
