import 'package:flutter/material.dart';
import 'package:narzin/core/constants.dart';

class CategoryTile extends StatelessWidget {
  const CategoryTile({
    super.key,
    required this.category_name,
    required this.onTap,
    required this.selectedIndex,
    required this.index,
  });

  final String? category_name;
  final void Function()? onTap;
  final int selectedIndex;
  final int index;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(left: 7),
        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 5),
        height: 40,
        constraints: const BoxConstraints(minWidth: 100),
        decoration: selectedIndex == index
            ? BoxDecoration(
            gradient: const LinearGradient(
              colors: [
                Color(0xff3084C2),
                Color(0xff5BB5EF),
              ],
            ),
            borderRadius: BorderRadius.circular(10))
            : BoxDecoration(
          color:Constants.lighterSecondaryColor,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: Constants.lighterSecondaryColor,
          ),
        ),
        child: Center(
          child: Text(
            category_name ?? '',
            style: selectedIndex == index ? const TextStyle(fontSize: 17, color: Colors.white, fontWeight: FontWeight.w600) :
            TextStyle(fontSize: 15, color: Constants.mainColor),
          ),
        ),
      ),
    );
  }
}