import 'dart:convert';
import 'dart:io';
import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:http/http.dart' as http;

class Helpers{

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

  static Future<String> imageUrlToBase64(String url) async {
    // حمل الصورة من الرابط
    // print("immmaaaaaaaggggeeeeee${url}");
    final response = await http.get(Uri.parse(url));

    if (response.statusCode == 200) {
      Uint8List bytes = response.bodyBytes;

      // حوّل ل base64
      String base64String = base64Encode(bytes);

      // detect نوع الصورة من الامتداد
      String? extension;
      if (url.endsWith(".png")) {
        extension = "png";
      } else if (url.endsWith(".jpg") || url.endsWith(".jpeg")) {
        extension = "jpeg";
      } else if (url.endsWith(".gif")) {
        extension = "gif";
      } else {
        extension = "*"; // unknown
      }

      // رجّع string بالـ Data URI format
      return "data:image/$extension;base64,$base64String";
    } else {
      throw Exception("Failed to load image: ${response.statusCode}");
    }
  }
  static Future<String> encodeFileToBase64(String filePath) async {
    final file = File(filePath);
    final bytes = await file.readAsBytes();
    return base64Encode(bytes);
  }
  static String addDataUriHeader(String base64String) {
    // لو فيه header خلاص سيبه
    if (base64String.startsWith("data:")) {
      return base64String;
    }

    // Detect type from magic number
    if (base64String.startsWith("/9j")) {
      return "data:image/jpeg;base64,$base64String";
    } else if (base64String.startsWith("iVBORw0K")) {
      return "data:image/png;base64,$base64String";
    } else if (base64String.startsWith("R0lGOD")) {
      return "data:image/gif;base64,$base64String";
    } else {
      // Default لو مش معروف
      return "data:image/*;base64,$base64String";
    }
  }
  static Uint8List decodeBase64Image(String base64String) {
    // لو string فيها header "data:image..."
    if (base64String.contains(",")) {
      base64String = base64String.split(",").last;
    }

    return base64Decode(base64String);
  }
  static Future<String> imageToBase64(String imagePath, {bool withMime = false}) async {
    final File imageFile = File(imagePath);

    if (!await imageFile.exists()) {
      throw Exception("File not found at path: $imagePath");
    }

    final bytes = await imageFile.readAsBytes();
    final base64Str = base64Encode(bytes);

    if (withMime) {
      final extension = imagePath.split('.').last.toLowerCase();
      final mimeType = _getMimeType(extension);
      return 'data:$mimeType;base64,$base64Str';
    }

    return base64Str;
  }
  static String _getMimeType(String extension) {
    switch (extension) {
      case 'png':
        return 'image/png';
      case 'jpg':
      case 'jpeg':
        return 'image/jpeg';
      case 'gif':
        return 'image/gif';
      case 'webp':
        return 'image/webp';
      default:
        return 'application/octet-stream';
    }
  }

  static void showColoredToast({String? message, Color? color, Color? textColor}) {
    Fluttertoast.showToast(
      msg: message ?? 'No Toast',
      toastLength: Toast.LENGTH_SHORT,
      backgroundColor: color,
      textColor: textColor ?? Colors.white,
    );

  }



}