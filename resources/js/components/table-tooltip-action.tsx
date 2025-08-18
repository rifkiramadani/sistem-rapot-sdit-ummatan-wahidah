import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { ReactNode } from 'react';

interface TableTooltipActionProps {
    children: ReactNode;
    info: string;
    delay?: number;
    side?: 'top' | 'right' | 'bottom' | 'left';
}
const TableTooltipAction = ({ children, info, delay = 1500, side = 'left' }: TableTooltipActionProps) => {
    return (
        <Tooltip delayDuration={delay}>
            <TooltipTrigger asChild>{children}</TooltipTrigger>
            <TooltipContent side={side}>
                <p>{info}</p>
            </TooltipContent>
        </Tooltip>
    );
};

export default TableTooltipAction;
