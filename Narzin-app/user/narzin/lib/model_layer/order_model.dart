class PlaceOrderModel {
  bool? status;
  String? message;
  Data? data;

  PlaceOrderModel({this.status, this.message, this.data});

  PlaceOrderModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    data = json['data'] != null ? Data.fromJson(json['data']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['status'] = status;
    data['message'] = message;
    if (this.data != null) {
      data['data'] = this.data!.toJson();
    }
    return data;
  }
}

class Data {
  Payment? payment;

  Data({this.payment});

  Data.fromJson(Map<String, dynamic> json) {
    payment =
    json['payment'] != null ? Payment.fromJson(json['payment']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    if (payment != null) {
      data['payment'] = payment!.toJson();
    }
    return data;
  }
}

class Payment {
  String? type;
  String? paymentUrl;
  TransactionParams? transactionParams;

  Payment({this.type, this.paymentUrl, this.transactionParams});

  Payment.fromJson(Map<String, dynamic> json) {
    type = json['type'];
    paymentUrl = json['payment_url'];
    transactionParams = json['transaction_params'] != null
        ? TransactionParams.fromJson(json['transaction_params'])
        : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['type'] = type;
    data['payment_url'] = paymentUrl;
    if (transactionParams != null) {
      data['transaction_params'] = transactionParams!.toJson();
    }
    return data;
  }
}

class TransactionParams {
  String? tERMINAL;
  String? tRTYPE;
  String? aMOUNT;
  String? tIMESTAMP;
  String? nONCE;
  String? cURRENCY;
  String? oRDER;
  String? dESC;
  String? mERCHNAME;
  String? mERCHURL;
  String? eMAIL;
  String? cOUNTRY;
  String? mERCHGMT;
  String? bACKREF;
  String? nOTIFYURL;

  TransactionParams(
      {this.tERMINAL,
        this.tRTYPE,
        this.aMOUNT,
        this.tIMESTAMP,
        this.nONCE,
        this.cURRENCY,
        this.oRDER,
        this.dESC,
        this.mERCHNAME,
        this.mERCHURL,
        this.eMAIL,
        this.cOUNTRY,
        this.mERCHGMT,
        this.bACKREF,
        this.nOTIFYURL});

  TransactionParams.fromJson(Map<String, dynamic> json) {
    tERMINAL = json['TERMINAL'];
    tRTYPE = json['TRTYPE'];
    aMOUNT = json['AMOUNT'];
    tIMESTAMP = json['TIMESTAMP'];
    nONCE = json['NONCE'];
    cURRENCY = json['CURRENCY'];
    oRDER = json['ORDER'];
    dESC = json['DESC'];
    mERCHNAME = json['MERCH_NAME'];
    mERCHURL = json['MERCH_URL'];
    eMAIL = json['EMAIL'];
    cOUNTRY = json['COUNTRY'];
    mERCHGMT = json['MERCH_GMT'];
    bACKREF = json['BACKREF'];
    nOTIFYURL = json['NOTIFY_URL'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['TERMINAL'] = tERMINAL;
    data['TRTYPE'] = tRTYPE;
    data['AMOUNT'] = aMOUNT;
    data['TIMESTAMP'] = tIMESTAMP;
    data['NONCE'] = nONCE;
    data['CURRENCY'] = cURRENCY;
    data['ORDER'] = oRDER;
    data['DESC'] = dESC;
    data['MERCH_NAME'] = mERCHNAME;
    data['MERCH_URL'] = mERCHURL;
    data['EMAIL'] = eMAIL;
    data['COUNTRY'] = cOUNTRY;
    data['MERCH_GMT'] = mERCHGMT;
    data['BACKREF'] = bACKREF;
    data['NOTIFY_URL'] = nOTIFYURL;
    return data;
  }
}