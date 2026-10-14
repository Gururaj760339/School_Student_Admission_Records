
@extends('layouts.app')

@section('title', 'Select a Class')

@section('content')
<style>
    .admission-select {
        --as-primary: #4f46e5;
        --as-primary-dark: #4338ca;
        --as-text: #111827;
        --as-muted: #6b7280;
        --as-border: #e5e7eb;
        --as-background: #f9fafb;

        width: 100%;
        color: var(--as-text);
        font-family: inherit;
    }

    .admission-select *,
    .admission-select *::before,
    .admission-select *::after {
        box-sizing: border-box;
    }

    /* Page header */
    .admission-select .as-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 26px;
    }

    .admission-select .as-header-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        flex-shrink: 0;
        border-radius: 15px;
        background: #eef2ff;
        color: var(--as-primary);
    }

    .admission-select .as-header-icon svg {
        width: 28px;
        height: 28px;
    }

    .admission-select .as-header h1 {
        margin: 0 0 7px;
        color: var(--as-text);
        font-size: clamp(23px, 3vw, 29px);
        font-weight: 750;
        letter-spacing: -.7px;
        line-height: 1.3;
    }

    .admission-select .as-subtitle {
        max-width: 650px;
        margin: 0;
        color: var(--as-muted);
        font-size: 14px;
        line-height: 1.8;
    }

    /* Main container */
    .admission-select .as-panel {
        overflow: hidden;
        border: 1px solid var(--as-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
    }

    .admission-select .as-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 21px 24px;
        border-bottom: 1px solid var(--as-border);
        background: #fcfcfd;
    }

    .admission-select .as-panel-heading {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .admission-select .as-panel-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        border-radius: 11px;
        background: #eef2ff;
        color: var(--as-primary);
    }

    .admission-select .as-panel-icon svg {
        width: 21px;
        height: 21px;
    }

    .admission-select .as-panel-header h2 {
        margin: 0 0 5px;
        color: var(--as-text);
        font-size: 17px;
        font-weight: 700;
    }

    .admission-select .as-panel-header p {
        margin: 0;
        color: var(--as-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .admission-select .as-panel-body {
        padding: 24px;
    }

    /* Class grid */
    .admission-select .as-class-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .admission-select .as-class-card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 13px;
        background: #fff;
        transition:
            border-color .2s ease,
            box-shadow .2s ease,
            transform .2s ease;
    }

    .admission-select .as-class-card:hover {
        border-color: #c7d2fe;
        box-shadow: 0 7px 20px rgba(79, 70, 229, .07);
        transform: translateY(-2px);
    }

    .admission-select .as-class-card.as-default {
        border-color: #a5b4fc;
        box-shadow: 0 0 0 1px rgba(99, 102, 241, .08);
    }

    .admission-select .as-class-card.as-default::before {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        height: 3px;
        background: var(--as-primary);
        content: "";
    }

    .admission-select .as-class-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 82px;
        padding: 17px;
        border-bottom: 1px solid #f0f1f5;
        background: #fcfcff;
    }

    .admission-select .as-class-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 43px;
        height: 43px;
        flex-shrink: 0;
        border: 1px solid #e0e7ff;
        border-radius: 12px;
        background: #eef2ff;
        color: var(--as-primary-dark);
        font-size: 17px;
        font-weight: 750;
    }

    .admission-select .as-class-heading {
        min-width: 0;
        flex: 1;
    }

    .admission-select .as-class-name {
        display: block;
        margin: 0;
        color: #1f2937;
        font-size: 15px;
        font-weight: 750;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .admission-select .as-default-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 5px;
        padding: 3px 7px;
        border: 1px solid #c7d2fe;
        border-radius: 20px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 10px;
        font-weight: 700;
    }

    .admission-select .as-default-badge svg {
        width: 11px;
        height: 11px;
    }

    /* Section list */
    .admission-select .as-section-list {
        display: flex;
        flex-direction: column;
        gap: 9px;
        margin: 0;
        padding: 15px;
        list-style: none;
    }

    .admission-select .as-section-list li {
        min-width: 0;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .admission-select .as-section-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 46px;
        padding: 11px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 9px;
        background: #fff;
        color: #374151;
        text-decoration: none;
        transition:
            background .18s ease,
            border-color .18s ease,
            color .18s ease;
    }

    .admission-select .as-section-link:hover {
        border-color: #a5b4fc;
        background: #f5f6ff;
        color: var(--as-primary-dark);
    }

    .admission-select .as-section-link:focus-visible {
        outline: 3px solid rgba(79, 70, 229, .2);
        outline-offset: 2px;
    }

    .admission-select .as-section-label {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .admission-select .as-section-label::before {
        width: 7px;
        height: 7px;
        flex-shrink: 0;
        border-radius: 50%;
        background: #a5b4fc;
        content: "";
    }

    .admission-select .as-section-link:hover .as-section-label::before {
        background: var(--as-primary);
    }

    .admission-select .as-section-arrow {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 27px;
        height: 27px;
        flex-shrink: 0;
        border-radius: 7px;
        background: #f3f4f6;
        color: #6b7280;
        font-size: 16px;
        transition: background .18s ease, color .18s ease;
    }

    .admission-select .as-section-link:hover .as-section-arrow {
        background: #e0e7ff;
        color: var(--as-primary-dark);
    }

    /* Section row + copy link */
    .admission-select .as-section-row {
        display: flex;
        align-items: stretch;
        gap: 8px;
        min-width: 0;
    }

    .admission-select .as-section-row .as-section-link {
        flex: 1 1 auto;
        min-width: 0;
    }

    .admission-select .as-copy-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        flex: 0 0 auto;
        padding: 0 10px;
        border: 1px solid #c7d2fe;
        border-radius: 9px;
        background: #eef2ff;
        color: var(--as-primary-dark);
        font-family: inherit;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
        cursor: pointer;
        transition:
            background .18s ease,
            border-color .18s ease,
            color .18s ease;
    }

    .admission-select .as-copy-link:hover {
        border-color: #a5b4fc;
        background: #e0e7ff;
    }

    .admission-select .as-copy-link:focus-visible {
        outline: 3px solid rgba(79, 70, 229, .2);
        outline-offset: 2px;
    }

    .admission-select .as-copy-link svg {
        width: 13px;
        height: 13px;
        flex-shrink: 0;
    }

    /* Copy feedback toast */
    .admission-select .as-copy-toast {
        position: fixed;
        left: 50%;
        bottom: 24px;
        z-index: 60;
        max-width: calc(100vw - 32px);
        padding: 11px 18px;
        border-radius: 10px;
        background: #111827;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .22);
        opacity: 0;
        visibility: hidden;
        transform: translate(-50%, 12px);
        transition:
            opacity .2s ease,
            transform .2s ease,
            visibility .2s ease;
        pointer-events: none;
    }

    .admission-select .as-copy-toast.is-visible {
        opacity: 1;
        visibility: visible;
        transform: translate(-50%, 0);
    }

    .admission-select .as-copy-toast.as-copy-toast-success {
        background: #047857;
    }

    .admission-select .as-copy-toast.as-copy-toast-error {
        background: #b91c1c;
    }

    /* Empty section state */
    .admission-select .as-no-sections {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin: 15px;
        padding: 13px;
        border: 1px dashed #d1d5db;
        border-radius: 9px;
        background: #f9fafb;
        color: var(--as-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .admission-select .as-no-sections svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    /* No classes alert */
    .admission-select .as-empty-state {
        padding: 42px 22px;
        text-align: center;
        border: 1px dashed #d1d5db;
        border-radius: 12px;
        background: #fafbfc;
    }

    .admission-select .as-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        margin: 0 auto 16px;
        border-radius: 16px;
        background: #eef2ff;
        color: var(--as-primary);
    }

    .admission-select .as-empty-icon svg {
        width: 29px;
        height: 29px;
    }

    .admission-select .as-empty-state h3 {
        margin: 0 0 8px;
        color: #1f2937;
        font-size: 17px;
        font-weight: 750;
    }

    .admission-select .as-empty-state p {
        max-width: 460px;
        margin: 0 auto;
        color: var(--as-muted);
        font-size: 13px;
        line-height: 1.8;
    }

    .admission-select .as-admin-link {
        display: inline;
        color: var(--as-primary);
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .admission-select .as-admin-link:hover {
        color: var(--as-primary-dark);
    }

    @media (max-width: 1050px) {
        .admission-select .as-class-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .admission-select .as-header {
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 20px;
        }

        .admission-select .as-header-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
        }

        .admission-select .as-header-icon svg {
            width: 24px;
            height: 24px;
        }

        .admission-select .as-subtitle {
            font-size: 13px;
        }

        .admission-select .as-panel-header {
            padding: 17px;
        }

        .admission-select .as-panel-body {
            padding: 15px;
        }

        .admission-select .as-class-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 14px;
        }

        .admission-select .as-class-card-header {
            min-height: 74px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .admission-select *,
        .admission-select *::before,
        .admission-select *::after {
            transition: none !important;
        }
    }
</style>

<div class="admission-select">

    {{-- Page Header --}}
    <div class="as-header">
        <div class="as-header-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 21h18"/>
                <path d="M5 21V7l8-4v18"/>
                <path d="M19 21V11l-6-4"/>
                <path d="M9 9h.01M9 12h.01M9 15h.01M9 18h.01"/>
                <path d="M16 13h.01M16 16h.01M16 19h.01"/>
            </svg>
        </div>

        <div>
            <h1>New Student Admission</h1>
            <p class="as-subtitle">
                Select a class and section to begin the student admission process.
                Each section is stored in its own worksheet.
            </p>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="as-panel">

        <div class="as-panel-header">
            <div class="as-panel-heading">
                <div class="as-panel-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                        <path d="M8 7h8M8 11h6"/>
                    </svg>
                </div>

                <div>
                    <h2>Select Class &amp; Section</h2>
                    <p>Choose your preferred section to continue.</p>
                </div>
            </div>
        </div>

        <div class="as-panel-body">

            @if (empty($classes))
                <div class="as-empty-state">
                    <div class="as-empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.7"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/>
                            <path d="M5 21V7l8-4v18"/>
                            <path d="M19 21V11l-6-4"/>
                            <path d="M9 9h.01M9 12h.01M9 15h.01"/>
                        </svg>
                    </div>

                    <h3>No Classes Available</h3>

                    <p>
                        No classes are available yet. An administrator can add
                        classes and sections from the
                        <a class="as-admin-link"
                           href="{{ auth()->check() ? route('admin.classes.index') : route('login') }}">admin panel</a>.
                    </p>
                </div>
            @else
                <div class="as-class-grid">
                    @foreach ($classes as $group)
                        <div class="as-class-card {{ collect($group['sections'])->contains('option_slug', $default) ? 'is-default' : '' }}">

                            {{-- Class Card Header --}}
                            <div class="as-class-card-header">
                                <div class="as-class-avatar" aria-hidden="true">
                                    {{ \Illuminate\Support\Str::upper(
                                        \Illuminate\Support\Str::substr($group['name'], 0, 1)
                                    ) }}
                                </div>

                                <div class="as-class-heading">
                                    <span class="as-class-name">
                                        {{ $group['name'] }}
                                    </span>

                                    @if (collect($group['sections'])->contains('option_slug', $default))
                                        <span class="as-default-badge">
                                            <svg viewBox="0 0 24 24" fill="none"
                                                 stroke="currentColor" stroke-width="2.2"
                                                 stroke-linecap="round" stroke-linejoin="round"
                                                 aria-hidden="true">
                                                <path d="m5 12 4 4L19 6"/>
                                            </svg>
                                            Default Selection
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if (empty($group['sections']))
                                <div class="as-no-sections">
                                    <svg viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="1.7"
                                         stroke-linecap="round" stroke-linejoin="round"
                                         aria-hidden="true">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="M12 8v4M12 16h.01"/>
                                    </svg>

                                    <span>No sections available yet.</span>
                                </div>
                            @else
                                <ul class="as-section-list">
                                    @foreach ($group['sections'] as $section)
                                        <li>
                                            <div class="as-section-row">
                                                <a class="as-section-link"
                                                   href="{{ route('admission.create', ['class' => $section['option_slug']]) }}">

                                                    <span class="as-section-label">
                                                        @if ($section['name'])
                                                            Section {{ $section['name'] }}
                                                        @else
                                                            Start admission
                                                        @endif
                                                    </span>

                                                    <span class="as-section-arrow"
                                                          aria-hidden="true">→</span>
                                                </a>

                                                <button type="button"
                                                        class="as-copy-link"
                                                        data-admission-url="{{ route('admission.create', ['class' => $section['option_slug']]) }}"
                                                        aria-label="Copy admission link for {{ $group['name'] }}@if ($section['name']) Section {{ $section['name'] }}@endif">
                                                    <svg viewBox="0 0 24 24" fill="none"
                                                         stroke="currentColor" stroke-width="1.9"
                                                         stroke-linecap="round" stroke-linejoin="round"
                                                         aria-hidden="true">
                                                        <rect x="9" y="9" width="11" height="11" rx="2"/>
                                                        <path d="M5 15V5a2 2 0 0 1 2-2h10"/>
                                                    </svg>
                                                    <span>Copy Link</span>
                                                </button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    {{-- Copy link feedback --}}
    <div class="as-copy-toast" role="status" aria-live="polite" aria-atomic="true"></div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        'use strict';

        var page = document.querySelector('.admission-select');

        if (!page) {
            return;
        }

        var toast = page.querySelector('.as-copy-toast');
        var toastTimer = null;

        function showToast(message, isError) {
            if (!toast) {
                return;
            }

            toast.textContent = message;
            toast.classList.toggle('as-copy-toast-success', !isError);
            toast.classList.toggle('as-copy-toast-error', !!isError);
            toast.classList.add('is-visible');

            window.clearTimeout(toastTimer);
            toastTimer = window.setTimeout(function () {
                toast.classList.remove('is-visible');
            }, 2500);
        }

        function fallbackCopy(text) {
            var helper = document.createElement('textarea');
            helper.value = text;
            helper.setAttribute('readonly', '');
            helper.style.position = 'fixed';
            helper.style.top = '-9999px';
            helper.style.opacity = '0';
            document.body.appendChild(helper);
            helper.select();
            helper.setSelectionRange(0, helper.value.length);

            try {
                var copied = document.execCommand('copy');
                document.body.removeChild(helper);

                if (!copied) {
                    throw new Error('Copy command was rejected.');
                }

                return Promise.resolve();
            } catch (error) {
                if (helper.parentNode) {
                    document.body.removeChild(helper);
                }

                return Promise.reject(error);
            }
        }

        function copyText(text) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            }

            return fallbackCopy(text);
        }

        page.addEventListener('click', function (event) {
            var target = event.target;

            if (!target || typeof target.closest !== 'function') {
                return;
            }

            var button = target.closest('.as-copy-link');

            if (!button || !page.contains(button)) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var url = button.getAttribute('data-admission-url');

            if (!url) {
                showToast('Unable to copy link.', true);
                return;
            }

            copyText(url).then(function () {
                showToast('Link copied successfully!', false);
            }).catch(function () {
                showToast('Unable to copy link. Please copy it manually.', true);
            });
        });
    })();
</script>
@endpush
