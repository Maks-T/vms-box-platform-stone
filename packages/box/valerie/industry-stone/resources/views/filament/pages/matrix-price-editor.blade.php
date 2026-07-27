<x-filament-panels::page>
  <style>
    .fi-ta-group-header-row {
      background-color: #f3f4f6 !important;
      border-left: 4px solid #b8945a !important;
    }

    .fi-ta-group-header-row button {
      font-weight: 800 !important;
      color: #111827 !important;
      font-size: 0.8rem !important;
      letter-spacing: 0.7px;
    }

    .fi-ta-group-header-row svg {
      color: #b8945a !important;
    }

    .dark .fi-ta-group-header-row {
      background-color: #1f2937 !important;
      border-left: 4px solid #d4b483 !important;
    }

    .dark .fi-ta-group-header-row button {
      color: #f3f4f6 !important;
    }

    .dark .fi-ta-group-header-row svg {
      color: #d4b483 !important;
    }
  </style>

  {{ $this->table }}
</x-filament-panels::page>
