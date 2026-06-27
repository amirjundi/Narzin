import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

class PaymentWebView extends StatefulWidget {
  final String paymentUrl;

  const PaymentWebView({super.key, required this.paymentUrl});

  @override
  _PaymentWebViewState createState() => _PaymentWebViewState();
}

class _PaymentWebViewState extends State<PaymentWebView> {
  late final WebViewController _controller;

  @override
  void initState() {
    super.initState();

    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onNavigationRequest: (request) {
            final url = request.url;
            print("Navigated to: $url");

            Uri uri = Uri.parse(url);

            if (uri.host.contains("narzin.com") && uri.path == "/thank-you") {
              final status = uri.queryParameters['status'];
              final orderId = uri.queryParameters['orderId'];
              final amount = uri.queryParameters['amount'];
              final rrn = uri.queryParameters['rrn'];

              Navigator.pop(context, {
                "status": status,
                "orderId": orderId,
                "amount": amount,
                "rrn": rrn,
                "fullUrl": url,
              });

              return NavigationDecision.prevent;
            }

            return NavigationDecision.navigate;
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.paymentUrl));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Payment"),
      ),
      body: WebViewWidget(controller: _controller),
    );
  }
}
