import 'package:flutter/material.dart';
import 'package:connectivity_plus/connectivity_plus.dart';

class OfflineBanner extends StatefulWidget {
  final Widget child;
  const OfflineBanner({super.key, required this.child});

  @override
  State<OfflineBanner> createState() => _OfflineBannerState();
}

class _OfflineBannerState extends State<OfflineBanner> {
  bool _isOffline = false;

  @override
  void initState() {
    super.initState();
    Connectivity().onConnectivityChanged.listen((
      List<ConnectivityResult> results,
    ) {
      if (mounted) {
        setState(() {
          _isOffline =
              results.isEmpty ||
              results.every((result) => result == ConnectivityResult.none);
        });
      }
    });

    // Check initial state
    Connectivity().checkConnectivity().then((List<ConnectivityResult> results) {
      if (mounted) {
        setState(() {
          _isOffline =
              results.isEmpty ||
              results.every((result) => result == ConnectivityResult.none);
        });
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;

    return Column(
      children: [
        Expanded(child: widget.child),
        if (_isOffline)
          AnimatedContainer(
            duration: const Duration(milliseconds: 220),
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
            color: colorScheme.errorContainer,
            child: Text(
              'Tidak ada koneksi internet',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: colorScheme.onErrorContainer,
                fontWeight: FontWeight.w700,
                fontSize: 13,
              ),
            ),
          ),
      ],
    );
  }
}
