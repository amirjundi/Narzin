import 'dart:async';

import 'package:flutter/material.dart';

import '../../../../model_layer/home_blocks_model.dart';
import 'block_link.dart';

class CountdownBannerBlock extends StatefulWidget {
  final Map<String, dynamic> content;
  const CountdownBannerBlock({super.key, required this.content});

  @override
  State<CountdownBannerBlock> createState() => _CountdownBannerBlockState();
}

class _CountdownBannerBlockState extends State<CountdownBannerBlock> {
  Timer? _timer;
  Duration _remaining = Duration.zero;
  DateTime? _endsAt;

  @override
  void initState() {
    super.initState();
    _endsAt = DateTime.tryParse('${widget.content['ends_at']}');
    _tick();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) => _tick());
  }

  void _tick() {
    if (_endsAt == null) return;
    final remaining = _endsAt!.difference(DateTime.now());
    if (!mounted) return;
    setState(() => _remaining = remaining.isNegative ? Duration.zero : remaining);
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  String _pad(int n) => n.toString().padLeft(2, '0');

  @override
  Widget build(BuildContext context) {
    final text = widget.content['text'];
    if (_endsAt == null ||
        _remaining == Duration.zero ||
        text is! String ||
        text.isEmpty) {
      return const SizedBox.shrink();
    }
    final days = _remaining.inDays;
    final hours = _remaining.inHours % 24;
    final minutes = _remaining.inMinutes % 60;
    final seconds = _remaining.inSeconds % 60;

    return GestureDetector(
      onTap: () => handleBlockLink(context, widget.content['link']),
      child: Container(
        width: double.infinity,
        color: parseHexColor(widget.content['bg_color'], const Color(0xFF141923)),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
        child: Column(
          children: [
            Text(text,
                textAlign: TextAlign.center,
                style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: parseHexColor(
                        widget.content['text_color'], const Color(0xFFD4AF37)))),
            const SizedBox(height: 4),
            Directionality(
              textDirection: TextDirection.ltr,
              child: Text(
                '${_pad(days)}:${_pad(hours)}:${_pad(minutes)}:${_pad(seconds)}',
                style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    fontFeatures: const [FontFeature.tabularFigures()],
                    color: parseHexColor(
                        widget.content['text_color'], const Color(0xFFD4AF37))),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
