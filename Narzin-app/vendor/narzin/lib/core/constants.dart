import 'dart:ui';

class Constants{


  static Color secondaryColor = const Color(0xff3084C2);
  static Color lightSecondaryColor = const Color(0xffEAF3F9);
  static Color lighterSecondaryColor = const Color(0xffF5FDFF);
  static Color mainColor = const Color(0xff225E8A);
  static Color notSelectedPoint = const Color(0xffF0A440);
  static Color grey = const Color(0xff6B7280);

  static Map<String, int> orderItemStatusColors = {
    'pending':0xffF0A440,
    'completed':0xff91f086,
    'rejected':0xffff0000,
  };
  static List<String> orderItemStatus = [
    'pending',
    'completed',
    'rejected',
  ];

  static Map<int,String> orderStatus = {
    0:'pending',
    1:'returned',
    2:'completed',
    3:'cancelled',
};

  static String apiBaseUrl = const String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://admin.narzin.com/api/v1/',
  );

}