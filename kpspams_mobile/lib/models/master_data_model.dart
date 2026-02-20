class AreaMasterModel {
  final int id;
  final String name;
  final String slug;
  final int customerCount;

  AreaMasterModel({
    required this.id,
    required this.name,
    required this.slug,
    required this.customerCount,
  });

  factory AreaMasterModel.fromJson(Map<String, dynamic> json) {
    return AreaMasterModel(
      id: json['id'] ?? 0,
      name: json['name'] ?? '-',
      slug: json['slug'] ?? '-',
      customerCount: json['customer_count'] ?? 0,
    );
  }
}

class GolonganMasterModel {
  final int id;
  final String code;
  final String name;
  final int customersCount;
  final int tariffsCount;
  final int feesCount;

  GolonganMasterModel({
    required this.id,
    required this.code,
    required this.name,
    required this.customersCount,
    required this.tariffsCount,
    required this.feesCount,
  });

  factory GolonganMasterModel.fromJson(Map<String, dynamic> json) {
    return GolonganMasterModel(
      id: json['id'] ?? 0,
      code: json['code'] ?? '-',
      name: json['name'] ?? '-',
      customersCount: json['customers_count'] ?? 0,
      tariffsCount: json['tariffs_count'] ?? 0,
      feesCount: json['fees_count'] ?? 0,
    );
  }
}
