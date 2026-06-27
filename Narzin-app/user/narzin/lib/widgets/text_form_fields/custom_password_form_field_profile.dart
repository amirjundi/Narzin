import 'package:flutter/material.dart';

import '../../core/constants.dart';

class ProfileCustomPasswordFormField extends StatelessWidget {
  const ProfileCustomPasswordFormField({
    super.key,
    required this.title,
    required this.hint,
    required this.isVisible,
    required this.onTap,
    this.controller,
    this.validator,
    required this.editTitle,
    required this.isActive,
    this.onPressed
  });
  final String? Function(String?)? validator;
  final TextEditingController? controller;
  final String title;
  final String editTitle;
  final String hint;
  final bool isVisible;
  final void Function()? onTap;
  final bool isActive;
  final void Function()? onPressed;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              title,
              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15,color: Color(0xff6B7280)),
            ),
            InkWell(
              onTap: onPressed,
              child: Text(
                editTitle,
                style: TextStyle(fontWeight: FontWeight.w500, fontSize: 15,color: Constants.mainColor),
              ),
            ),
          ],
        ),
        const SizedBox(
          height: 10,
        ),
        TextFormField(
          enabled: !isActive,
          readOnly: isActive,
          validator: validator,
          controller: controller,
          obscureText: isVisible,
          decoration: InputDecoration(
              suffixIcon: IconButton(onPressed: onTap, icon: const Icon(Icons.visibility)),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: BorderSide(color: Colors.grey[300]!),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: BorderSide(color: Colors.grey[300]!),
              ),
              contentPadding: const EdgeInsets.symmetric(
                vertical: 10,
                horizontal: 10,
              ),
              filled: true,
              fillColor: Colors.white,
              hintText: hint,
              hintStyle: const TextStyle(color: Color(0x73000000), fontSize: 12)),
        ),
      ],
    );
  }
}