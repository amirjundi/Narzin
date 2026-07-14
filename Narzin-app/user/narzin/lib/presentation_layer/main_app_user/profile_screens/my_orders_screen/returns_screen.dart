import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../bussiness_logic/login_cubits/login_cubit.dart';
import '../../../../bussiness_logic/order_cubits/order_cubit.dart';
import '../../../../bussiness_logic/returns_cubits/returns_cubit.dart';
import '../../../../core/screen_sizing_constants.dart';
import '../../../../generated/l10n.dart';
import '../../../../model_layer/my_orders_model.dart';
import '../../../../model_layer/returns_model.dart';

/// Readable labels for the fixed set of return reasons the backend accepts.
/// The map key is the exact enum string sent to the API (do NOT localize
/// these — they're the API contract); the value is the localized
/// human-readable label shown in the UI.
Map<String, String> _returnReasonLabels(BuildContext context) => {
      'damaged': S.of(context).return_reason_damaged,
      'wrong_item': S.of(context).return_reason_wrong_item,
      'not_as_described': S.of(context).return_reason_not_as_described,
      'no_longer_needed': S.of(context).return_reason_no_longer_needed,
      'other': S.of(context).return_reason_other,
    };

/// Readable labels for the fixed set of return statuses the backend sends.
/// The map key is the exact enum string from the API; the value is the
/// localized human-readable label shown in the UI.
Map<String, String> _returnStatusLabels(BuildContext context) => {
      'requested': S.of(context).return_status_requested,
      'approved': S.of(context).return_status_approved,
      'rejected': S.of(context).return_status_rejected,
      'refunded': S.of(context).return_status_refunded,
    };

/// Payment statuses on an order that make it eligible to request a return for.
const List<String> kReturnEligiblePaymentStatuses = ['completed', 'processing'];

Color _returnStatusColor(String? status) {
  switch (status) {
    case 'requested':
      return Colors.amber;
    case 'approved':
      return Colors.blue;
    case 'rejected':
      return Colors.red;
    case 'refunded':
      return Colors.green;
    default:
      return Colors.grey;
  }
}

String _capitalize(String value) => value.isEmpty ? value : '${value[0].toUpperCase()}${value.substring(1)}';

class ReturnsScreen extends StatefulWidget {
  const ReturnsScreen({super.key});

  @override
  State<ReturnsScreen> createState() => _ReturnsScreenState();
}

class _ReturnsScreenState extends State<ReturnsScreen> {
  @override
  void initState() {
    super.initState();
    final token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
    context.read<ReturnsCubit>().fetchReturns(token: token);
    context.read<OrderCubit>().getMyOrder(token: token);
  }

  Future<void> _openRequestReturnSheet(MyOrder order) async {
    final reasonLabels = _returnReasonLabels(context);
    String selectedReason = reasonLabels.keys.first;
    final noteController = TextEditingController();

    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (sheetContext) {
        return StatefulBuilder(
          builder: (sheetContext, setSheetState) {
            return Padding(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 20,
                bottom: MediaQuery.of(sheetContext).viewInsets.bottom + 20,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      order.orderNumber ?? 'Order #${order.id}',
                      style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      S.of(sheetContext).reason_for_return,
                      style: const TextStyle(fontWeight: FontWeight.w600),
                    ),
                    ...reasonLabels.entries.map(
                      (entry) => RadioListTile<String>(
                        contentPadding: EdgeInsets.zero,
                        dense: true,
                        value: entry.key,
                        groupValue: selectedReason,
                        title: Text(entry.value),
                        onChanged: (value) {
                          if (value == null) return;
                          setSheetState(() => selectedReason = value);
                        },
                      ),
                    ),
                    const SizedBox(height: 8),
                    TextField(
                      controller: noteController,
                      maxLines: 2,
                      decoration: InputDecoration(
                        labelText: S.of(sheetContext).note_optional,
                        border: const OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: () async {
                          Navigator.of(sheetContext).pop();
                          await _submitReturn(
                            order: order,
                            reason: selectedReason,
                            note: noteController.text.trim().isEmpty ? null : noteController.text.trim(),
                          );
                        },
                        child: Text(S.of(sheetContext).submit_request),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Future<void> _submitReturn({
    required MyOrder order,
    required String reason,
    String? note,
  }) async {
    final token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
    final orderId = int.tryParse(order.id ?? '');
    if (orderId == null || orderId == 0) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(S.of(context).couldnt_identify_order)),
      );
      return;
    }
    final err = await context.read<ReturnsCubit>().requestReturn(
          token: token,
          orderId: orderId,
          reason: reason,
          note: note,
        );
    if (!mounted) return;
    if (err == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(S.of(context).return_requested_successfully)),
      );
      context.read<ReturnsCubit>().fetchReturns(token: token);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(err)),
      );
    }
  }

  Widget _buildMyReturns(BuildContext context, ReturnsModel? returnsModel, bool isLoading) {
    final items = returnsModel?.data ?? const <ReturnItem>[];

    if (isLoading && items.isEmpty) {
      return const Padding(
        padding: EdgeInsets.symmetric(vertical: 24),
        child: Center(child: CircularProgressIndicator()),
      );
    }

    if (items.isEmpty) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 16),
        child: Text(
          S.of(context).no_return_requests_yet,
          style: const TextStyle(color: Color(0xff4B5563)),
        ),
      );
    }

    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: items.length,
      itemBuilder: (context, index) {
        final item = items[index];
        final orderLabel = item.orderNumber ?? 'Order #${item.orderId}';
        final reasonLabel = _returnReasonLabels(context)[item.reason] ?? item.reason ?? '-';
        final statusColor = _returnStatusColor(item.status);
        final statusLabel = _returnStatusLabels(context)[item.status] ?? _capitalize(item.status ?? '-');

        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: Colors.grey[200]!),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      orderLabel,
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: statusColor.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: statusColor),
                    ),
                    child: Text(
                      statusLabel,
                      style: TextStyle(color: statusColor, fontSize: 12, fontWeight: FontWeight.w600),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text(
                '${S.of(context).reason_label_prefix}: $reasonLabel',
                style: TextStyle(color: Colors.grey[700], fontSize: 13),
              ),
              if (item.requestedAt != null) ...[
                const SizedBox(height: 4),
                Text(
                  '${S.of(context).requested_label_prefix}: ${item.requestedAt}',
                  style: TextStyle(color: Colors.grey[600], fontSize: 12),
                ),
              ],
            ],
          ),
        );
      },
    );
  }

  Widget _buildRequestReturn(BuildContext context) {
    final myOrders = context.read<OrderCubit>().myOrdersModel?.data?.data;
    final eligibleOrders = (myOrders ?? const <MyOrder>[])
        .where((order) => kReturnEligiblePaymentStatuses.contains(order.paymentStatus))
        .toList();

    if (eligibleOrders.isEmpty) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 16),
        child: Text(
          S.of(context).no_eligible_orders_for_return,
          style: const TextStyle(color: Color(0xff4B5563)),
        ),
      );
    }

    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: eligibleOrders.length,
      itemBuilder: (context, index) {
        final order = eligibleOrders[index];
        return Padding(
          padding: const EdgeInsets.symmetric(vertical: 5),
          child: InkWell(
            onTap: () => _openRequestReturnSheet(order),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.grey[200]!),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    order.orderNumber ?? 'Order #${order.id}',
                    style: const TextStyle(fontWeight: FontWeight.w600),
                  ),
                  const Icon(Icons.arrow_forward_ios_rounded, size: 16),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).returns,
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        automaticallyImplyLeading: false,
        leading: IconButton(
          onPressed: () {
            Navigator.canPop(context) ? Navigator.pop(context) : null;
          },
          icon: const Icon(Icons.arrow_back_ios_rounded),
        ),
        centerTitle: true,
      ),
      body: BlocBuilder<ReturnsCubit, ReturnsState>(
        builder: (context, state) {
          final returnsModel = context.read<ReturnsCubit>().returnsModel;
          final isLoading = context.read<ReturnsCubit>().isLoading;

          return SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Text(
                  S.of(context).my_returns,
                  style: const TextStyle(
                    fontSize: 17,
                    fontWeight: FontWeight.w500,
                    color: Color(0xff4B5563),
                  ),
                ),
                const SizedBox(height: 10),
                _buildMyReturns(context, returnsModel, isLoading),
                const SizedBox(height: 24),
                Text(
                  S.of(context).request_a_return,
                  style: const TextStyle(
                    fontSize: 17,
                    fontWeight: FontWeight.w500,
                    color: Color(0xff4B5563),
                  ),
                ),
                const SizedBox(height: 10),
                BlocBuilder<OrderCubit, OrderState>(
                  builder: (context, orderState) => _buildRequestReturn(context),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
