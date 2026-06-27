import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:narzin/generated/l10n.dart';

class Helpers {
  static String concatenateErrors(Map<String, dynamic> errors) {
    List<String> errorMessages = [];

    errors.forEach((key, value) {
      if (value is List) {
        for (var message in value) {
          errorMessages.add("$key: $message");
        }
      }
    });

    return errorMessages.join("\n"); // Join messages with a newline or any delimiter
  }

  static void showColoredToast({String? message, Color? color, Color? textColor}) {
    Fluttertoast.showToast(
      msg: message ?? 'No Toast',
      toastLength: Toast.LENGTH_SHORT,
      backgroundColor: color,
      textColor: textColor ?? Colors.white,
    );
  }

  static String formatFullAddress({
    required String fullAddress,
    required String city,
    required String country,
    required String street,
    String? apartmentNo,
    String? buildingNo,
  }) {
    List<String> addressParts = [
      "\"$fullAddress\"\n",
      "Street: $street\n",
      if (apartmentNo != null && apartmentNo.isNotEmpty) 'Apartment No: $apartmentNo\n',
      if (buildingNo != null && buildingNo.isNotEmpty) 'Building No: $buildingNo\n',
      "City: $city\n",
      "Country: $country\n",
    ];
    return addressParts.where((part) => part.isNotEmpty).join(',');
  }

  static String formatLangFullAddress({required String location}) {
      String loc = location.replaceAll('\n', '\t\t');
      return loc;
  }

  static Map<int, bool> wishlistItems = {};
  static Map<int, int> wishlistProducts = {};
  static Map<String, int> orderStatus = {
    "pending": 1,  // Pending (Active)
    "out for delivery": 2,  // Out for Delivery (Active)
    "delivered": 3, // Delivered (Completed)
    "cancelled": 9, // Cancelled (Inactive)
    "return": 0, // Return (Completed/Inactive)
  };
  static getOrderItemStatus(BuildContext context,String status) {
    switch (status) {
      case "pending":
        return S.of(context).pending;
      case "out for delivery":
        return S.of(context).out_for_delivery;
      case "delivered":
        return S.of(context).delivered;
      case "cancelled":
        return S.of(context).cancelled;
      case "return":
        return S.of(context).returned;
      default:
        return status;
    }
  }
}
