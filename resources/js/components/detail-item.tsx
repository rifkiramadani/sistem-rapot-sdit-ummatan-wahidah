const DetailItem = ({ label, value, className }: { label: string; value: string | number | null | undefined; className?: string }) => {
    return (
        <div className={className}>
            <p className="text-sm font-medium text-muted-foreground">{label}</p>
            <p className="text-base font-semibold">{value || '-'}</p>
        </div>
    );
};

export default DetailItem;
