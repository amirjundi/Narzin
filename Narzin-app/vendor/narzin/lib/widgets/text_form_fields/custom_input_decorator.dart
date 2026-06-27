import 'package:flutter/material.dart';

class CustomInputDecorator extends StatelessWidget {
  const CustomInputDecorator({
    super.key,
    this.child,
    this.title,
    this.hint,
    this.suffix,
  });

  final Widget? child;
  final String? title;
  final String? hint;
  final Widget? suffix;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start, // Align title to the left
      children: [
        if (title != null) // Only show title if not null
          Text(
            title!,
            style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 15),
          ),
        const SizedBox(height: 10),
        InputDecorator(
          decoration: InputDecoration(
            suffixIcon: suffix,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(5),
              borderSide: BorderSide(color: Colors.grey[300]!),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(5),
              borderSide: BorderSide(color: Colors.grey[300]!),
            ),
            contentPadding: const EdgeInsets.symmetric(
              vertical: 5,
              horizontal: 5,
            ),
            filled: true,
            fillColor: Colors.white,
            hintText: hint,
            hintStyle: const TextStyle(color: Color(0x73000000), fontSize: 12),
          ),
          child: child,
        ),
      ],
    );
  }
}