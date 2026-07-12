class ReturnsModel {
  final bool? status;
  final List<ReturnItem> data;

  ReturnsModel({this.status, required this.data});

  factory ReturnsModel.fromJson(Map<String, dynamic> json) {
    final rawList = json['data'];
    final list = (rawList is List)
        ? rawList.map((e) => ReturnItem.fromJson(e as Map<String, dynamic>)).toList()
        : <ReturnItem>[];
    return ReturnsModel(status: json['status'] as bool?, data: list);
  }
}

class ReturnItem {
  final int? id;
  final int? orderId;
  final String? reason;
  final String? status;
  final String? refundAmount;
  final String? requestedAt;
  final String? orderNumber;

  ReturnItem({
    this.id,
    this.orderId,
    this.reason,
    this.status,
    this.refundAmount,
    this.requestedAt,
    this.orderNumber,
  });

  factory ReturnItem.fromJson(Map<String, dynamic> json) {
    final order = json['order'];
    return ReturnItem(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}'),
      orderId: json['order_id'] is int ? json['order_id'] as int : int.tryParse('${json['order_id']}'),
      reason: json['reason']?.toString(),
      status: json['status']?.toString(),
      refundAmount: json['refund_amount']?.toString(),
      requestedAt: json['requested_at']?.toString(),
      orderNumber: (order is Map<String, dynamic>) ? order['order_number']?.toString() : null,
    );
  }
}
