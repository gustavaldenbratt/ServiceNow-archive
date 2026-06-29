import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg
            {...props}
            viewBox="0 0 40 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                d="M7 12.5C7 8.91 12.82 6 20 6s13 2.91 13 6.5v15C33 31.09 27.18 34 20 34S7 31.09 7 27.5v-15Z"
                fill="none"
                stroke="currentColor"
                strokeWidth="3"
                strokeLinejoin="round"
            />
            <path
                d="M7 12.5C7 16.09 12.82 19 20 19s13-2.91 13-6.5M7 20c0 3.59 5.82 6.5 13 6.5S33 23.59 33 20"
                fill="none"
                stroke="currentColor"
                strokeWidth="3"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}
