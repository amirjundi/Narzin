import 'package:flutter_test/flutter_test.dart';
import 'package:narzin/model_layer/returns_model.dart';

void main() {
  test('parses returns with the eager-loaded order relation', () {
    final model = ReturnsModel.fromJson({
      'status': true,
      'data': [
        {
          'id': 5,
          'order_id': 42,
          'reason': 'damaged',
          'status': 'requested',
          'refund_amount': null,
          'requested_at': '2026-07-12T10:00:00Z',
          'order': {'order_number': 'ORD-042'},
        },
      ],
    });

    expect(model.status, true);
    expect(model.data.length, 1);
    expect(model.data[0].id, 5);
    expect(model.data[0].orderId, 42);
    expect(model.data[0].reason, 'damaged');
    expect(model.data[0].status, 'requested');
    expect(model.data[0].orderNumber, 'ORD-042');
  });

  test('tolerates a missing order relation', () {
    final model = ReturnsModel.fromJson({
      'status': true,
      'data': [
        {'id': 6, 'order_id': 7, 'reason': 'other', 'status': 'refunded', 'refund_amount': '50.00', 'requested_at': '2026-07-11'},
      ],
    });

    expect(model.data[0].orderNumber, isNull);
    expect(model.data[0].refundAmount, '50.00');
  });

  test('tolerates empty/absent data', () {
    final model = ReturnsModel.fromJson({'status': true});
    expect(model.data, isEmpty);
  });
}
