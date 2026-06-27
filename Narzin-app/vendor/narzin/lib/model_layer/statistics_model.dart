class StatisticsModel {
  String? status;
  String? message;
  Data? data;

  StatisticsModel({this.status, this.message, this.data});

  StatisticsModel.fromJson(Map<String, dynamic> json) {
    status = json['status'].toString();
    message = json['message']?.toString();
    data = json['data'] != null ? Data.fromJson(json['data']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> dataMap = <String, dynamic>{};
    dataMap['status'] = status;
    dataMap['message'] = message;
    if (data != null) {
      dataMap['data'] = data!.toJson();
    }
    return dataMap;
  }
}

class Data {
  String? pending;
  String? processing;
  String? shipped;
  String? delivered;
  String? totalRevenue;
  String? totalCost;
  String? totalProfit;
  String? recentOrders;

  Data({
    this.pending,
    this.processing,
    this.shipped,
    this.delivered,
    this.totalRevenue,
    this.totalCost,
    this.totalProfit,
    this.recentOrders,
  });

  Data.fromJson(Map<String, dynamic> json) {
    pending = json['pending']?.toString();
    processing = json['processing']?.toString();
    shipped = json['shipped']?.toString();
    delivered = json['delivered']?.toString();
    totalRevenue = json['total_revenue']?.toString();
    totalCost = json['total_cost']?.toString();
    totalProfit = json['total_profit']?.toString();
    recentOrders = json['recent_orders']?.toString();
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> dataMap = <String, dynamic>{};
    dataMap['pending'] = pending;
    dataMap['processing'] = processing;
    dataMap['shipped'] = shipped;
    dataMap['delivered'] = delivered;
    dataMap['total_revenue'] = totalRevenue;
    dataMap['total_cost'] = totalCost;
    dataMap['total_profit'] = totalProfit;
    dataMap['recent_orders'] = recentOrders;
    return dataMap;
  }
}