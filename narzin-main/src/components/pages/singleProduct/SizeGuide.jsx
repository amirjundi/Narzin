export default function SizeGuide({ sizeChart }) {
  if (!sizeChart || !sizeChart.columns?.length || !sizeChart.rows?.length) {
    return null;
  }
  const { columns, rows, unit = "cm" } = sizeChart;
  return (
    <section className="my-6">
      <h3 className="text-lg font-semibold mb-2">Size guide ({unit})</h3>
      <div className="overflow-x-auto">
        <table className="min-w-full text-sm border">
          <thead>
            <tr className="bg-gray-50">
              <th className="border px-3 py-2 text-left">Size</th>
              {columns.map((c) => (
                <th key={c} className="border px-3 py-2 text-left">{c}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {rows.map((r, i) => (
              <tr key={i}>
                <td className="border px-3 py-2 font-medium">{r.size}</td>
                {columns.map((c) => (
                  <td key={c} className="border px-3 py-2">
                    {r.values?.[c] ?? "—"}
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </section>
  );
}
