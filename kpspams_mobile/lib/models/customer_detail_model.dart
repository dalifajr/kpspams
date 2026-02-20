class CustomerDetailModel {
  final int id;
  final String customerCode;
  final String name;
  final String? phone;
  final String? address;
  final String? areaName;
  final String? golonganName;

  CustomerDetailModel({
    required this.id,
    required this.customerCode,
    required this.name,
    this.phone,
    this.address,
    this.areaName,
    this.golonganName,
  });

  factory CustomerDetailModel.fromJson(Map<String, dynamic> json) {
    return CustomerDetailModel(
      id: json['id'] ?? 0,
      customerCode: json['customer_code'] ?? '',
      name: json['name'] ?? '',
      phone: json['phone'],
      address: json['address'],
      areaName: json['area']?['name'],
      golonganName: json['golongan']?['name'],
    );
  }
}
