import 'package:flutter/material.dart';

class CustomRadioWidget<T> extends StatelessWidget {
  final T value;
  final T groupValue;
  final ValueChanged<T> onChanged;
  final double width;
  final double height;
  final Color unselectedInnerColor;
  final Color unselectedBorderColor;

  const CustomRadioWidget({super.key, 
    required this.value,
    required this.groupValue,
    required this.onChanged,
    required this.unselectedBorderColor,
    required this.unselectedInnerColor,
    this.width = 32,
    this.height = 32,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(8.0),
      child: GestureDetector(
        onTap: () {
          onChanged(this.value);
        },
        child: Container(
          height: this.height,
          width: this.width,
          padding: const EdgeInsets.all(1),
          decoration: ShapeDecoration(
            shape: const CircleBorder(),
            gradient: value != groupValue
                ? LinearGradient(
                    colors: [unselectedBorderColor, unselectedBorderColor],
                  )
                : const LinearGradient(
                    colors: [
                      Color(0xFF5BB5EF),
                      Color(0xFF3084C2),
                    ],
                  ),
          ),
          child: Center(
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              height: this.height,
              width: this.width,
              margin: const EdgeInsets.all(1),
              decoration: BoxDecoration(
                border: Border.all(color: unselectedInnerColor, width: 2),
                shape: BoxShape.circle,
                gradient: LinearGradient(
                  colors: value == groupValue
                      ? [
                          const Color(0xFF3084C2),
                          const Color(0xFF5BB5EF),
                        ]
                      : [
                    unselectedInnerColor,
                    unselectedInnerColor,
                        ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
