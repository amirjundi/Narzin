class AttributesModel {
  bool? status;
  List<Data>? data;

  AttributesModel({this.status, this.data});

  AttributesModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
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
  String? nameArabic;
  String? nameGerman;
  String? createdAt;
  String? updatedAt;
  String? type;
  List<String>? typeValues;

  Data(
      {this.id,
        this.nameArabic,
        this.nameGerman,
        this.createdAt,
        this.updatedAt,
        this.type,
        this.typeValues});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    nameArabic = json['name_arabic'];
    nameGerman = json['name_german'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    type = json['type'];
    if(json['type_values'] != null){
      typeValues = json['type_values'].cast<String>();
    }else{
      typeValues = json['type_values'];
    }

  }

}