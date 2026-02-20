class UserModel {
  final int id;
  final String name;
  final String phoneNumber;
  final String role;
  final String status;
  final bool isApproved;
  final bool mustUpdatePassword;
  final int? areaId;
  final String? area;
  final String? addressShort;
  final String? avatarUrl;

  UserModel({
    required this.id,
    required this.name,
    required this.phoneNumber,
    required this.role,
    required this.status,
    required this.isApproved,
    required this.mustUpdatePassword,
    this.areaId,
    this.area,
    this.addressShort,
    this.avatarUrl,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      phoneNumber: json['phone_number'] ?? '',
      role: json['role'] ?? 'user',
      status: json['status'] ?? 'pending',
      isApproved: json['is_approved'] ?? false,
      mustUpdatePassword: json['must_update_password'] ?? false,
      areaId: json['area_id'],
      area: json['area'],
      addressShort: json['address_short'],
      avatarUrl: json['avatar_url'],
    );
  }
}
