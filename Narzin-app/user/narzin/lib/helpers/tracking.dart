import 'dart:convert';
import 'dart:math';

import 'package:http/http.dart' as http;
import 'package:narzin/core/constants.dart';

/// Best-effort behavioral tracking. Mirrors what the web app already sends so
/// mobile traffic shows up in the admin funnel / attribution / abandoned-cart
/// analytics. Every call is wrapped in try/catch and MUST NEVER throw or
/// block/delay the UI — failures are silently swallowed.
class TrackingService {
  // One session id per app launch (kept in memory), shared by session + cart
  // + place-order so the backend can stitch the funnel and attribute the
  // order to this session.
  static String? _sessionId;

  static String get sessionId {
    _sessionId ??=
        'app_${DateTime.now().millisecondsSinceEpoch}_${Random().nextInt(0x7fffffff)}';
    return _sessionId!;
  }

  static Future<void> trackSession() async {
    try {
      await http.post(
        Uri.parse('${Constants.apiBaseUrl}track/session'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({'session_id': sessionId}),
      );
    } catch (_) {
      // tracking must never break the app
    }
  }

  static Future<void> trackCartAdd({
    required int productId,
    int? variantId,
    required int quantity,
    double? unitPrice,
  }) async {
    try {
      await http.post(
        Uri.parse('${Constants.apiBaseUrl}track/cart'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'session_id': sessionId,
          'action': 'add',
          'product_id': productId,
          'variant_id': variantId,
          'quantity': quantity,
          'unit_price': unitPrice,
        }),
      );
    } catch (_) {
      // tracking must never break the app
    }
  }
}
