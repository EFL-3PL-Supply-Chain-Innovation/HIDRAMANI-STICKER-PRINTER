import 'package:flutter/material.dart';

errorSnackBar(BuildContext context, String text) {
  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(
      content: Text(
        text,
        style: TextStyle(
          color: Theme.of(context).colorScheme.background,
        ),
      ),
      backgroundColor: Colors.red,
      duration: const Duration(seconds: 2),
    ),
  );
}