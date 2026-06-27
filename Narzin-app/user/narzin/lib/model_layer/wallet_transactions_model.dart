class WalletTransactionModel {
  bool? status;
  String? message;
  List<Data>? data;

  WalletTransactionModel({this.status, this.data, this.message});

  WalletTransactionModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    if (json['data'] != null) {
      data = <Data>[];
      json['data'].forEach((v) {
        data!.add(Data.fromJson(v));
      });
    }
  }
}

class Data {
  int? id;
  int? userId;
  int? walletId;
  String? type;
  String? amount;
  String? createdAt;
  String? updatedAt;

  Data(
      {this.id,
        this.userId,
        this.walletId,
        this.type,
        this.amount,
        this.createdAt,
        this.updatedAt});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    userId = json['user_id'];
    walletId = json['wallet_id'];
    type = json['type'];
    amount = json['amount'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }
}