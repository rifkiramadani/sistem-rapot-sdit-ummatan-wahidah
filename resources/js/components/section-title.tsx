const SectionTitle = ({ title }: { title: string }) => {
    return (
        <div className="mt-6 first:mt-0 md:col-span-2">
            <h3 className="text-lg font-medium">{title}</h3>
            <hr className="mt-2" />
        </div>
    );
};

export default SectionTitle;
