function object_compare(obj1, obj2) {
    if (typeof obj1 != typeof obj2) return false;

    switch (typeof obj1) {
        case 'object':
            for (let p in obj1)
                if (!object_compare(obj1[p], obj2[p])) return false;
            for (let p in obj2)
                if (typeof obj1[p] != typeof obj2[p]) return false;
            break;
        case 'function':
            break;
        default:
            if (obj1 != obj2) return false;
    }
    
	return true;
};

export {
    object_compare
};
