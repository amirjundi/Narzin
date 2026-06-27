import 'package:flutter/material.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/generated/l10n.dart';

class StatsItem extends StatelessWidget {
  const StatsItem({
    super.key,
    this.asset,
    this.title,
    this.value,

  });
  final String? asset;
  final String? title;
  final String? value;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: ScreenSizing.width * 0.45,
      padding: EdgeInsets.symmetric(vertical: 10, horizontal: 5),
      constraints: BoxConstraints(
        minWidth: 120,
      ),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(15),
        boxShadow: [BoxShadow(color: Colors.grey[300]!, spreadRadius: 0.2, offset: Offset(0, 1))],
      ),
      child: Row(
        children: [
          SizedBox(width: 5),
          Image.asset( asset??
              Assets.appIconsUsers,
            height: 40,
            fit: BoxFit.contain,
          ),
          SizedBox(width: 5),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Text(
                  title?? '',
                  style: const TextStyle(
                    color: Colors.grey,
                    fontSize: 13,
                    fontWeight: FontWeight.w300,
                  ),
                ),
                Text(
                  value?? '',
                  style: const TextStyle(
                    color: Colors.black,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}