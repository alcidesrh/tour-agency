import list from './list';
import create from './create';
import del from './delete';
import update from './update';

export default {
    namespaced: true,
    modules: {
        list,
        create,
        del,
        update
    }
}
