class ProfileModel {
  bool? status;
  String? message;
  Data? data;

  ProfileModel({this.status, this.message, this.data});

  ProfileModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    data = json['data'] != null ? Data.fromJson(json['data']) : null;
  }

}

class Data {
  User? user;
  // Devices? devices;

  Data({this.user,
    // this.devices
  });

  Data.fromJson(Map<String, dynamic> json) {
    user = json['user'] != null ? User.fromJson(json['user']) : null;
    // devices =
    // json['devices'] != null ? new Devices.fromJson(json['devices']) : null;
  }

}

class User {
  int? id;
  String? name;
  String? email;
  String? emailVerifiedAt;
  String? createdAt;
  String? updatedAt;

  User(
      {this.id,
        this.name,
        this.email,
        this.emailVerifiedAt,
        this.createdAt,
        this.updatedAt});

  User.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    email = json['email'];
    emailVerifiedAt = json['email_verified_at'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name'] = name;
    data['email'] = email;
    data['email_verified_at'] = emailVerifiedAt;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    return data;
  }
}

class Devices {
  CurrentDevice? currentDevice;
  List<AllDevices>? allDevices;
  int? totalDevices;
  int? webSessions;
  int? apiTokens;

  Devices(
      {this.currentDevice,
        this.allDevices,
        this.totalDevices,
        this.webSessions,
        this.apiTokens});

  Devices.fromJson(Map<String, dynamic> json) {
    currentDevice = json['current_device'] != null
        ? CurrentDevice.fromJson(json['current_device'])
        : null;
    if (json['all_devices'] != null) {
      allDevices = <AllDevices>[];
      json['all_devices'].forEach((v) {
        allDevices!.add(AllDevices.fromJson(v));
      });
    }
    totalDevices = json['total_devices'];
    webSessions = json['web_sessions'];
    apiTokens = json['api_tokens'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    if (currentDevice != null) {
      data['current_device'] = currentDevice!.toJson();
    }
    if (allDevices != null) {
      data['all_devices'] = allDevices!.map((v) => v.toJson()).toList();
    }
    data['total_devices'] = totalDevices;
    data['web_sessions'] = webSessions;
    data['api_tokens'] = apiTokens;
    return data;
  }
}

class CurrentDevice {
  String? deviceType;
  String? os;
  String? browser;
  String? userAgent;

  CurrentDevice({this.deviceType, this.os, this.browser, this.userAgent});

  CurrentDevice.fromJson(Map<String, dynamic> json) {
    deviceType = json['device_type'];
    os = json['os'];
    browser = json['browser'];
    userAgent = json['user_agent'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['device_type'] = deviceType;
    data['os'] = os;
    data['browser'] = browser;
    data['user_agent'] = userAgent;
    return data;
  }
}

class AllDevices {
  String? id;
  String? ipAddress;
  CurrentDevice? deviceInfo;
  String? lastActivity;
  bool? isCurrent;
  String? type;
  String? lastUsed;
  String? createdAt;

  AllDevices(
      {this.id,
        this.ipAddress,
        this.deviceInfo,
        this.lastActivity,
        this.isCurrent,
        this.type,
        this.lastUsed,
        this.createdAt});

  AllDevices.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    ipAddress = json['ip_address'];
    deviceInfo = json['device_info'] != null
        ? CurrentDevice.fromJson(json['device_info'])
        : null;
    lastActivity = json['last_activity'];
    isCurrent = json['is_current'];
    type = json['type'];
    lastUsed = json['last_used'];
    createdAt = json['created_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['ip_address'] = ipAddress;
    if (deviceInfo != null) {
      data['device_info'] = deviceInfo!.toJson();
    }
    data['last_activity'] = lastActivity;
    data['is_current'] = isCurrent;
    data['type'] = type;
    data['last_used'] = lastUsed;
    data['created_at'] = createdAt;
    return data;
  }
}