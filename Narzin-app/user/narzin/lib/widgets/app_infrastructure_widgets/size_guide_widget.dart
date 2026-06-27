import 'package:flutter/material.dart';
import 'package:narzin/model_layer/single_product_model.dart';

class SizeGuideWidget extends StatelessWidget {
  final SizeChart? sizeChart;
  const SizeGuideWidget({super.key, required this.sizeChart});

  @override
  Widget build(BuildContext context) {
    final sc = sizeChart;
    if (sc == null || sc.columns.isEmpty || sc.rows.isEmpty) {
      return const SizedBox.shrink();
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Text('Size guide (${sc.unit})',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
        ),
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: DataTable(
            columns: [
              const DataColumn(label: Text('Size')),
              ...sc.columns.map((c) => DataColumn(label: Text(c))),
            ],
            rows: sc.rows.map((r) {
              return DataRow(cells: [
                DataCell(Text(r.size)),
                ...sc.columns.map((c) =>
                    DataCell(Text(r.values[c]?.toString() ?? '—'))),
              ]);
            }).toList(),
          ),
        ),
      ],
    );
  }
}
