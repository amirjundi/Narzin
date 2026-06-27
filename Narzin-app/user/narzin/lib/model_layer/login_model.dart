class LoginModel {
  bool? status;
  String? message;
  Data? data;
  Errors? errors;

  LoginModel({this.status, this.message, this.data, this.errors});

  LoginModel.fromJson(Map<String, dynamic> json) {
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
  bool? verificationRequired;
  int? userId;
  String? resendVerificationUrl;

  Data(
      {this.user,
        this.token,
        this.tokenType,
        this.verificationRequired,
        this.userId,
        this.resendVerificationUrl});

  Data.fromJson(Map<String, dynamic> json) {
    user = json['user'] != null ? User.fromJson(json['user']) : null;
    token = json['token'];
    tokenType = json['token_type'];
    verificationRequired = json['verification_required'];
    userId = json['user_id'];
    resendVerificationUrl = json['resend_verification_url'];
  }

}

class User {
  int? id;
  String? name;
  String? email;
  String? createdAt;
  String? emailVerifiedAt;

  User({this.id, this.name, this.email, this.createdAt, this.emailVerifiedAt});

  User.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    email = json['email'];
    createdAt = json['created_at'];
    emailVerifiedAt = json['email_verified_at'];
  }

}

class Errors {
  List<String>? email;
  List<String>? password;

  Errors({this.email, this.password});

  Errors.fromJson(Map<String, dynamic> json) {
    email = json['email']?.cast<String>();
    password = json['password']?.cast<String>();
  }

}