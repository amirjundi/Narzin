import 'package:flutter/material.dart';

class CustomPasswordFormField extends StatelessWidget {
  const CustomPasswordFormField({
    super.key,
    required this.title,
    required this.hint,
    required this.isVisible,
    required this.onTap,
    this.controller,
    this.validator
  });
  final String? Function(String?)? validator;
  final TextEditingController? controller;
  final String title;
  final String hint;
  final bool isVisible;
  final void Function()? onTap;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Text(
          title,
          style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 15),
        ),
        const SizedBox(
          height: 10,
        ),
        TextFormField(
          validator: validator,
          controller: controller,
          obscureText: isVisible,
          decoration: InputDecoration(
              suffixIcon: IconButton(onPressed: onTap, icon: Icon(Icons.visibility)),
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