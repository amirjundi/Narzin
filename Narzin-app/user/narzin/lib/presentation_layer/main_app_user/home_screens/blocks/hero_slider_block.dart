import 'dart:async';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import 'block_link.dart';

class HeroSliderBlock extends StatefulWidget {
  final Map<String, dynamic> content;
  const HeroSliderBlock({super.key, required this.content});

  @override
  State<HeroSliderBlock> createState() => _HeroSliderBlockState();
}

class _HeroSliderBlockState extends State<HeroSliderBlock> {
  final PageController _controller = PageController();
  Timer? _timer;
  int _active = 0;
  late final List<Map> _slides;

  @override
  void initState() {
    super.initState();
    _slides = (widget.content['slides'] is List
            ? widget.content['slides'] as List
            : [])
        .whereType<Map>()
        .where((s) => s['image'] is String && (s['image'] as String).isNotEmpty)
        .toList();
    if (_slides.length > 1) {
      _timer = Timer.periodic(const Duration(seconds: 4), (_) {
        if (!mounted || !_controller.hasClients) return;
        final next = (_active + 1) % _slides.length;
        _controller.animateToPage(next,
            duration: const Duration(milliseconds: 400),
            curve: Curves.easeOut);
      });
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_slides.isEmpty) return const SizedBox.shrink();
    return AspectRatio(
      aspectRatio: 2,
      child: Stack(
        children: [
          PageView.builder(
            controller: _controller,
            itemCount: _slides.length,
            onPageChanged: (index) => setState(() => _active = index),
            itemBuilder: (context, index) {
              final slide = _slides[index];
              return GestureDetector(
                onTap: () => handleBlockLink(context, slide['link']),
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    CachedNetworkImage(
                      imageUrl: slide['image'],
                      fit: BoxFit.cover,
                      errorWidget: (_, __, ___) =>
                          Container(color: const Color(0xFFF7F9FB)),
                    ),
                    if (slide['title'] is String || slide['subtitle'] is String)
                      Container(
                        alignment: AlignmentDirectional.bottomStart,
                        padding: const EdgeInsets.all(14),
                        decoration: const BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.bottomCenter,
                            end: Alignment.center,
                            colors: [Color(0xB3141923), Colors.transparent],
                          ),
                        ),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if (slide['title'] is String)
                              Text(slide['title'],
                                  style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold)),
                            if (slide['subtitle'] is String)
                              Text(slide['subtitle'],
                                  style: const TextStyle(
                                      color: Color(0xFFC5A880), fontSize: 13)),
                          ],
                        ),
                      ),
                  ],
                ),
              );
            },
          ),
          if (_slides.length > 1)
            Positioned(
              bottom: 8,
              left: 0,
              right: 0,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  for (var i = 0; i < _slides.length; i++)
                    Container(
                      width: i == _active ? 16 : 6,
                      height: 6,
                      margin: const EdgeInsets.symmetric(horizontal: 2),
                      decoration: BoxDecoration(
                        color: i == _active
                            ? Colors.white
                            : Colors.white.withValues(alpha: 0.5),
                        borderRadius: BorderRadius.circular(3),
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
