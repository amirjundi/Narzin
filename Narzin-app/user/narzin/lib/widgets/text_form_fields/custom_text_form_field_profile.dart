import 'package:flutter/material.dart';
import 'package:narzin/core/constants.dart';

class ProfileCustomTextFormField extends StatelessWidget {
  const ProfileCustomTextFormField({
    super.key,
    required this.title,
    required this.hint,
    this.controller,
    this.validator,
    required this.editTitle,
    required this.isActive,
    this.onTap,
  });
  final String? Function(String?)? validator;
  final TextEditingController? controller;
  final String title;
  final String hint;
  final bool isActive;
  final String editTitle;
  final void Function()? onTap;



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
              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14,color: Color(0xff6B7280)),
            ),
            InkWell(
              onTap: onTap,
              child: Text(
                editTitle,
                style: TextStyle(fontWeight: FontWeight.w500, fontSize: 14,color: Constants.mainColor),
              ),
            ),
          ],
        ),
        const SizedBox(
          height: 5,
        ),
        TextFormField(
          enabled: !isActive,
          readOnly: isActive,
          validator: validator,
          controller: controller,
          decoration: InputDecoration(
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