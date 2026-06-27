import 'dart:ui';

class Constants{


  static Color secondaryColor = const Color(0xff3084C2);
  static Color lightSecondaryColor = const Color(0xffEAF3F9);
  static Color lighterSecondaryColor = const Color(0xffF5FDFF);
  static Color mainColor = const Color(0xff225E8A);
  static Color notSelectedPoint = const Color(0xffF0A440);
  static Color grey = const Color(0xff6B7280);


  static String apiBaseUrl = const String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://admin.narzin.com/api/v1/',
  );

}