class RegisterModel {
  bool? status;
  String? message;
  Data? data;
  Errors? errors;

  RegisterModel({this.status, this.message, this.data, this.errors});

  RegisterModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    data = json['data'] != null ? Data.fromJson(json['data']) : null;
    errors =
    json['errors'] != null ? Errors.fromJson(json['errors']) : null;
  }
}

class Data {
  User? user;
  String? token;
  String? tokenType;
  VerificationStatus? verificationStatus;

  Data({this.user, this.token, this.tokenType, this.verificationStatus});

  Data.fromJson(Map<String, dynamic> json) {
    user = json['user'] != null ? User.fromJson(json['user']) : null;
    token = json['token'];
    tokenType = json['token_type'];
    verificationStatus = json['verification_status'] != null
        ? VerificationStatus.fromJson(json['verification_status'])
        : null;
  }
}

class User {
  int? id;
  String? name;
  String? email;
  String? createdAt;

  User({this.id, this.name, this.email, this.createdAt});

  User.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    email = json['email'];
    createdAt = json['created_at'];
  }
}

class VerificationStatus {
  bool? verified;
  int? userId;
  String? message;

  VerificationStatus({this.verified, this.userId, this.message});

  VerificationStatus.fromJson(Map<String, dynamic> json) {
    verified = json['verified'];
    userId = json['user_id'];
    message = json['message'];
  }
}

class Errors {
  List<String>? email;
  List<String>? password;
  List<String>? userTypeId;
  List<String>? name;

  Errors({this.email, this.password, this.userTypeId, this.name});

  Errors.fromJson(Map<String, dynamic> json) {
    email = json['email']?.cast<String>();
    password = json['password']?.cast<String>();
    userTypeId = json['user_type_id']?.cast<String>();
    name = json['name']?.cast<String>();
  }
}