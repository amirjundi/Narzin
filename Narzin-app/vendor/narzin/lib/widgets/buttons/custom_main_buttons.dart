import 'package:flutter/material.dart';

import '../../core/constants.dart';

class CustomSignIn_UpThree extends StatelessWidget {
  CustomSignIn_UpThree({
    super.key,
    required this.title,
    this.customizeChild,
    this.ontap,
    this.color,
    this.textColor,
  });
  Color? color;
  Color? textColor;
  String title;
  Widget? customizeChild;
  void Function()? ontap;
  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      style: ElevatedButton.styleFrom(
        surfaceTintColor: Colors.white,
        minimumSize:
        Size(MediaQuery.of(context).size.width, 55),
        maximumSize:
        Size(MediaQuery.of(context).size.width, 55),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10),
          side: BorderSide(
            color: color?? Constants.mainColor,
            width: 1.6
          ),
        ),
        backgroundColor: color?? Colors.white,
      ),
      onPressed: ontap,
      child: customizeChild??Text(
        title,
        style: TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.w800,
          color:textColor?? Constants.mainColor,
        ),
      ),
    );
  }
}

class CustomSignIn_UpTwo extends StatelessWidget {
  CustomSignIn_UpTwo({
    super.key,
    required this.title,
    this.customizeChild,
    this.ontap,
    this.color,
    this.textColor,
  });
  Color? color;
  Color? textColor;
  String title;
  Widget? customizeChild;
  void Function()? ontap;
  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      style: ElevatedButton.styleFrom(
        surfaceTintColor: Colors.white,
        minimumSize:
        Size(MediaQuery.of(context).size.width, 55),
        maximumSize:
        Size(MediaQuery.of(context).size.width, 55),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10),
          side: BorderSide(
            color: color?? Constants.lightSecondaryColor,
          ),
        ),
        backgroundColor: color?? Constants.lightSecondaryColor,
      ),
      onPressed: ontap,
      child: customizeChild??Text(
        title,
        style: TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.w800,
          color:textColor?? Constants.mainColor,
        ),
      ),
    );
  }
}

class CustomSignIn_UpOne extends StatelessWidget {
  CustomSignIn_UpOne({
    super.key,
    required this.title,
    this.customizeChild,
    this.ontap,
    this.color,
  });
  Color? color;
  String title;
  Widget? customizeChild;
  void Function()? ontap;

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height*0.074;
    return Padding(
      padding: const EdgeInsets.only(bottom: 15),
      child: ElevatedButton(
        style: ElevatedButton.styleFrom(
          surfaceTintColor: Colors.transparent,
          elevation: 5,
          minimumSize:
          Size(MediaQuery.of(context).size.width, 55),
          maximumSize:
          Size(MediaQuery.of(context).size.width, 55),
          shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10)),
          backgroundColor: color?? Constants.mainColor,
        ),
        onPressed: ontap,
        child: customizeChild??Text(
          title,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: Colors.white,
          ),
        ),
      ),
    );
  }
}